<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.bank.view') || $user->can('organization.full_access'); }
    public function view(User $user, BankAccount $account): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->can('finance.bank.manage') || $user->can('organization.full_access'); }
    public function update(User $user, BankAccount $account): bool { return $this->create($user); }
    public function delete(User $user, BankAccount $account): bool { return $this->create($user); }
}
