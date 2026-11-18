<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.account.view') || $user->can('organization.full_access'); }
    public function view(User $user, Account $account): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->can('finance.account.manage') || $user->can('organization.full_access'); }
    public function update(User $user, Account $account): bool { return $this->create($user); }
    public function delete(User $user, Account $account): bool { return $this->create($user) && ! $account->bankAccount()->exists() && ! $account->cashAccount()->exists(); }
}
