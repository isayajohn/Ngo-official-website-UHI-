<?php

namespace App\Policies;

use App\Models\CashAccount;
use App\Models\User;
use App\Services\DepartmentAccessService;

class CashAccountPolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.cash.view') || $user->can('organization.full_access'); }
    public function view(User $user, CashAccount $account): bool { return $this->viewAny($user) && (! $account->department || app(DepartmentAccessService::class)->canAccessDepartment($user, $account->department)); }
    public function create(User $user): bool { return $user->can('finance.cash.manage') || $user->can('organization.full_access'); }
    public function update(User $user, CashAccount $account): bool { return $this->create($user) && (! $account->department || app(DepartmentAccessService::class)->canAccessDepartment($user, $account->department)); }
    public function delete(User $user, CashAccount $account): bool { return $this->update($user, $account); }
}
