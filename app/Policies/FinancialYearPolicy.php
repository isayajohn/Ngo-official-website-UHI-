<?php

namespace App\Policies;

use App\Models\FinancialYear;
use App\Models\User;

class FinancialYearPolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.year.view') || $user->can('organization.full_access'); }
    public function view(User $user, FinancialYear $year): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->can('finance.year.manage') || $user->can('organization.full_access'); }
    public function update(User $user, FinancialYear $year): bool { return $user->can('finance.year.manage') || $user->can('organization.full_access'); }
    public function delete(User $user, FinancialYear $year): bool { return $this->update($user, $year) && $year->status !== 'CLOSED'; }
}
