<?php

namespace App\Policies;

use App\Models\PerformanceRatingScale;
use App\Models\User;

class PerformanceRatingScalePolicy
{
    public function viewAny(User $user): bool { return $user->can('performance.view') || $user->can('performance.manage_scale') || $user->can('organization.full_access'); }
    public function view(User $user, PerformanceRatingScale $scale): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->can('performance.manage_scale') || $user->can('organization.full_access'); }
    public function update(User $user, PerformanceRatingScale $scale): bool { return $this->create($user); }
    public function delete(User $user, PerformanceRatingScale $scale): bool { return $this->create($user); }
}
