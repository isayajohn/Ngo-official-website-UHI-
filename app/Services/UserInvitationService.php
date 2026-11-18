<?php

namespace App\Services;

use App\Enums\UserInvitationStatus;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

class UserInvitationService
{
    public function invite(array $data, User $inviter): array
    {
        abort_unless($inviter->can('user.invite'), 403);
        $this->ensureRateLimit($inviter, 'create', 10);
        $email = mb_strtolower(trim($data['email']));

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'An account already uses this email address.']);
        }

        $pending = UserInvitation::query()
            ->where('email', $email)
            ->where('status', UserInvitationStatus::Pending->value)
            ->where('expires_at', '>', now())
            ->exists();

        if ($pending) {
            throw ValidationException::withMessages(['email' => 'An invitation is already pending for this email address.']);
        }

        $teamMember = filled($data['team_member_id'] ?? null)
            ? TeamMember::query()->findOrFail($data['team_member_id'])
            : null;

        if ($teamMember && ($teamMember->user || $teamMember->legacyUser)) {
            throw ValidationException::withMessages(['team_member_id' => 'This team member already has a linked user account.']);
        }

        if ($teamMember?->email && mb_strtolower($teamMember->email) !== $email) {
            throw ValidationException::withMessages(['email' => 'The invitation email must match the linked team member profile.']);
        }

        if (empty($data['role_id'])) {
            throw ValidationException::withMessages(['role_id' => 'Choose a role for the invited account.']);
        }

        abort_unless($inviter->can('user.assign_role') || $inviter->can('role.manage'), 403);
        $role = Role::query()->where('guard_name', 'web')->findOrFail($data['role_id']);
        abort_if($role->name === 'Super Admin' && ! $inviter->can('role.manage'), 403);

        $departmentIds = collect([$data['primary_department_id'] ?? null, ...((array) ($data['department_ids'] ?? []))])
            ->filter()->map(fn ($id) => (int) $id)->unique();
        if ($departmentIds->isNotEmpty()) {
            abort_unless($inviter->can('user.assign_department') || $inviter->can('organization.full_access'), 403);
            $activeDepartmentCount = \App\Models\Department::query()->where('active', true)->whereKey($departmentIds)->count();
            if ($activeDepartmentCount !== $departmentIds->count()) {
                throw ValidationException::withMessages(['primary_department_id' => 'Choose active departments only.']);
            }
        }

        $token = Str::random(64);
        $invitation = DB::transaction(function () use ($data, $email, $inviter, $teamMember, $token): UserInvitation {
            $invitation = UserInvitation::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $email,
                'team_member_id' => $teamMember?->getKey(),
                'invited_by_user_id' => $inviter->getKey(),
                'role_id' => $data['role_id'] ?? null,
                'primary_department_id' => $data['primary_department_id'] ?? null,
                'token_hash' => Hash::make($token),
                'status' => UserInvitationStatus::Pending,
                'expires_at' => now()->addHours((int) config('ughi.user_invitation_expiry_hours', 48)),
            ]);

            $departmentIds = collect($data['department_ids'] ?? [])
                ->reject(fn ($id) => (int) $id === (int) ($data['primary_department_id'] ?? 0))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
            $invitation->departments()->sync($departmentIds);

            DepartmentActivity::record(null, $inviter->getKey(), 'user.invitation_created', $invitation, [
                'email' => $email,
                'role_id' => $invitation->role_id,
                'primary_department_id' => $invitation->primary_department_id,
            ]);

            return $invitation->load(['role', 'primaryDepartment', 'inviter']);
        });

        return [$invitation, $this->send($invitation, $token, $inviter, 'user.invitation_sent')];
    }

    public function resend(UserInvitation $invitation, User $actor): bool
    {
        abort_unless($actor->can('user.invitation.resend'), 403);
        $this->ensureRateLimit($actor, 'resend', 5);
        if (! in_array($invitation->effectiveStatus(), [UserInvitationStatus::Pending, UserInvitationStatus::Expired], true)) {
            throw ValidationException::withMessages(['invitation' => 'Only pending or expired invitations can be resent.']);
        }

        if (User::query()->where('email', $invitation->email)->exists()) {
            throw ValidationException::withMessages(['invitation' => 'An account already uses this email address.']);
        }

        $token = Str::random(64);
        $invitation->forceFill([
            'token_hash' => Hash::make($token),
            'status' => UserInvitationStatus::Pending,
            'expires_at' => now()->addHours((int) config('ughi.user_invitation_expiry_hours', 48)),
            'revoked_at' => null,
        ])->save();

        DepartmentActivity::record(null, $actor->getKey(), 'user.invitation_resent', $invitation, ['email' => $invitation->email]);

        return $this->send($invitation->load(['role', 'primaryDepartment', 'inviter']), $token, $actor, 'user.invitation_sent');
    }

    public function revoke(UserInvitation $invitation, User $actor): void
    {
        if ($invitation->status !== UserInvitationStatus::Pending) {
            throw ValidationException::withMessages(['invitation' => 'Only pending invitations can be revoked.']);
        }

        $invitation->forceFill([
            'status' => UserInvitationStatus::Revoked,
            'revoked_at' => now(),
        ])->save();

        DepartmentActivity::record(null, $actor->getKey(), 'user.invitation_revoked', $invitation);
    }

    public function findValidInvitation(int $invitationId, string $token): UserInvitation
    {
        $invitation = UserInvitation::query()
            ->with(['role', 'primaryDepartment', 'departments', 'teamMember', 'inviter'])
            ->where('status', UserInvitationStatus::Pending->value)
            ->where('expires_at', '>', now())
            ->find($invitationId);

        if (! $invitation || ! Hash::check($token, $invitation->token_hash)) {
            throw (new ModelNotFoundException())->setModel(UserInvitation::class);
        }

        return $invitation;
    }

    public function accept(UserInvitation $invitation, string $token, string $password): User
    {
        if (! $invitation->isPendingAndValid() || ! Hash::check($token, $invitation->token_hash)) {
            throw ValidationException::withMessages(['invitation' => 'This invitation is invalid, expired, or has already been used.']);
        }

        if (User::query()->where('email', $invitation->email)->exists()) {
            throw ValidationException::withMessages(['invitation' => 'An account already uses this email address. Contact your administrator.']);
        }

        return DB::transaction(function () use ($invitation, $password): User {
            $user = User::query()->create([
                'first_name' => $invitation->first_name,
                'last_name' => $invitation->last_name,
                'name' => trim($invitation->first_name.' '.$invitation->last_name),
                'email' => $invitation->email,
                'team_member_id' => $invitation->team_member_id,
                'password' => $password,
                'password_changed_at' => now(),
                'must_change_password' => false,
                'email_verified_at' => now(),
                'status' => 'ACTIVE',
                'is_active' => true,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);

            if ($invitation->role) {
                $user->assignRole($invitation->role);
            }

            $departmentIds = $invitation->departments->modelKeys();
            if ($invitation->primary_department_id) {
                $departmentIds[] = $invitation->primary_department_id;
            }

            $user->departments()->sync(collect($departmentIds)->unique()->mapWithKeys(
                fn (int $departmentId): array => [$departmentId => [
                    'is_primary' => $departmentId === (int) $invitation->primary_department_id,
                    'active' => true,
                ]],
            )->all());

            $invitation->forceFill([
                'status' => UserInvitationStatus::Accepted,
                'accepted_at' => now(),
                'token_hash' => Hash::make(Str::random(64)),
            ])->save();

            DepartmentActivity::record(null, $user->getKey(), 'user.invitation_accepted', $invitation, [
                'email' => $user->email,
                'role_id' => $invitation->role_id,
                'primary_department_id' => $invitation->primary_department_id,
            ]);

            return $user;
        });
    }

    private function send(UserInvitation $invitation, string $token, User $actor, string $auditEvent): bool
    {
        try {
            Notification::route('mail', $invitation->email)
                ->notify(new UserInvitationNotification($invitation, $token));

            DepartmentActivity::record(null, $actor->getKey(), $auditEvent, $invitation, ['email' => $invitation->email]);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Unable to send a UGHI user invitation email.', [
                'invitation_id' => $invitation->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }
    }

    private function ensureRateLimit(User $actor, string $action, int $maximumAttempts): void
    {
        $key = "user-invitations:{$action}:{$actor->getKey()}";

        if (RateLimiter::tooManyAttempts($key, $maximumAttempts)) {
            throw ValidationException::withMessages(['email' => 'Too many invitation attempts. Please try again in a minute.']);
        }

        RateLimiter::hit($key, 60);
    }
}
