<?php

namespace App\Policies;

use App\Models\InvolvementApplication;
use App\Models\User;

class InvolvementApplicationPolicy
{
    public static function canViewType(User $user, string $type): bool
    {
        $legacyModule = match ($type) { 'volunteer' => 'membership', 'partnership' => 'partner', default => null };

        return $user->can($type.'.view') || ($legacyModule && $user->can($legacyModule.'.view'));
    }

    public static function canUpdateType(User $user, string $type): bool
    {
        $legacyModule = match ($type) { 'volunteer' => 'membership', 'partnership' => 'partner', default => null };

        return $user->can($type.'.update') || ($legacyModule && $user->can($legacyModule.'.update'));
    }

    public function viewAny(User $user): bool
    {
        return collect(array_keys(InvolvementApplication::TYPES))->contains(fn (string $type) => self::canViewType($user, $type));
    }

    public function view(User $user, InvolvementApplication $application): bool
    {
        return self::canViewType($user, $application->type);
    }

    public function update(User $user, InvolvementApplication $application): bool
    {
        return self::canUpdateType($user, $application->type);
    }

    public function delete(): bool
    {
        return false;
    }
}
