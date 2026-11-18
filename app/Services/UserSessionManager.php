<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSessionManager
{
    public function available(): bool
    {
        return config('session.driver') === 'database'
            && Schema::connection(config('session.connection'))->hasTable(config('session.table', 'sessions'));
    }

    /** @return Collection<int, object> */
    public function forUser(User $user, ?string $currentSessionId = null): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        $cutoff = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;

        return $this->connection()->table($this->table())
            ->where('user_id', $user->getKey())
            ->where('last_activity', '>=', $cutoff)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $session) use ($currentSessionId): object {
                $session->is_current = $currentSessionId !== null && hash_equals($currentSessionId, (string) $session->id);
                $session->management_key = $this->managementKey((string) $session->id);
                $session->last_active_at = Carbon::createFromTimestamp((int) $session->last_activity);
                $session->browser = Str::limit(trim((string) ($session->user_agent ?: 'Unknown browser')), 180);

                return $session;
            });
    }

    public function countFor(User $user): int
    {
        if (! $this->available()) {
            return 0;
        }

        $cutoff = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;

        return $this->connection()->table($this->table())
            ->where('user_id', $user->getKey())
            ->where('last_activity', '>=', $cutoff)
            ->count();
    }

    public function revokeOne(User $user, string $managementKey, ?string $currentSessionId = null): bool
    {
        if (! $this->available()) {
            return false;
        }

        $sessionId = $this->connection()->table($this->table())
            ->where('user_id', $user->getKey())
            ->pluck('id')
            ->first(fn (string $id) => hash_equals($this->managementKey($id), $managementKey));

        if (! $sessionId || ($currentSessionId !== null && hash_equals($currentSessionId, $sessionId))) {
            return false;
        }

        $deleted = $this->connection()->table($this->table())->where('user_id', $user->getKey())->where('id', $sessionId)->delete();

        if ($deleted > 0) {
            $this->rotateRememberToken($user);
        }

        return $deleted > 0;
    }

    public function revokeOthers(User $user, string $currentSessionId): int
    {
        return $this->revokeAll($user, $currentSessionId);
    }

    /** Revoke every stored session except the current request's session, if supplied. */
    public function revokeAll(User $user, ?string $protectedSessionId = null): int
    {
        if (! $this->available()) {
            return 0;
        }

        $query = $this->connection()->table($this->table())->where('user_id', $user->getKey());

        if ($protectedSessionId !== null) {
            $query->where('id', '!=', $protectedSessionId);
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            $this->rotateRememberToken($user);
        }

        return $deleted;
    }

    private function rotateRememberToken(User $user): void
    {
        $user->forceFill(['remember_token' => Str::random(60)])->save();
    }

    private function connection(): Connection
    {
        return DB::connection(config('session.connection'));
    }

    private function table(): string
    {
        return (string) config('session.table', 'sessions');
    }

    private function managementKey(string $sessionId): string
    {
        return hash_hmac('sha256', $sessionId, (string) config('app.key'));
    }
}
