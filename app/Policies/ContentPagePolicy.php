<?php

namespace App\Policies;

use App\Models\ContentPage;
use App\Models\User;

class ContentPagePolicy
{
    public function viewAny(User $user): bool { return $user->can('page.view'); }
    public function view(User $user, ContentPage $page): bool
    {
        return match ($page->path) {
            'safeguarding' => $user->can('safeguarding.view'),
            'privacy-policy', 'terms' => $user->can('settings.manage'),
            default => $user->can('page.view'),
        };
    }
    public function create(User $user): bool { return $user->can('page.create'); }
    public function update(User $user, ContentPage $page): bool
    {
        return match ($page->path) {
            'safeguarding' => $user->can('safeguarding.update'),
            'privacy-policy', 'terms' => $user->can('settings.manage'),
            default => $user->can('page.update'),
        };
    }
    public function delete(User $user, ContentPage $page): bool { return $user->can('page.delete') && ! in_array($page->path, ['privacy-policy', 'safeguarding', 'terms']); }
    public function restore(User $user, ContentPage $page): bool { return $user->can('page.restore'); }
    public function forceDelete(User $user, ContentPage $page): bool { return false; }
}
