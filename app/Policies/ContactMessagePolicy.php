<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool { return $user->can('contact.view'); }
    public function view(User $user, ContactMessage $message): bool { return $user->can('contact.view'); }
    public function update(User $user, ContactMessage $message): bool { return $user->can('contact.update'); }
    public function delete(User $user, ContactMessage $message): bool { return $user->can('contact.delete'); }
}
