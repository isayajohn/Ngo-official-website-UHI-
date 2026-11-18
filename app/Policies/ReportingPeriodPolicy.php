<?php

namespace App\Policies;

use App\Models\ReportingPeriod;
use App\Models\User;

class ReportingPeriodPolicy
{
    public function viewAny(User $user): bool { return $user->can('performance.view') || $user->can('performance.manage_target') || $user->can('mel.dashboard.view') || $user->can('mel.target.view') || $user->can('mel.target.manage') || $user->can('organization.full_access'); }
    public function view(User $user, ReportingPeriod $period): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->can('performance.manage_target') || $user->can('mel.target.manage') || $user->can('organization.full_access'); }
    public function update(User $user, ReportingPeriod $period): bool { return $this->create($user); }
    public function delete(User $user, ReportingPeriod $period): bool { return $this->create($user) && ! $period->targets()->exists() && ! $period->results()->exists(); }
}
