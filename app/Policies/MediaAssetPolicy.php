<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool { return $user->can('media.view'); }
    public function view(User $user, MediaAsset $asset): bool { return $user->can('media.view'); }
    public function create(User $user): bool { return $user->can('media.create'); }
    public function update(User $user, MediaAsset $asset): bool { return $user->can('media.update'); }
    public function delete(User $user, MediaAsset $asset): bool { return $user->can('media.delete'); }
    public function restore(User $user, MediaAsset $asset): bool { return $user->can('media.restore'); }
    public function forceDelete(User $user, MediaAsset $asset): bool { return false; }
}
