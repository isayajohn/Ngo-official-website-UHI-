<?php

namespace App\Policies;

use App\Models\EditorialItem;
use App\Models\User;

class EditorialItemPolicy
{
    public function viewAny(User $user): bool
    {
        return collect(array_keys(EditorialItem::TYPES))->contains(fn (string $type) => $user->can($type.'.view'));
    }

    public function view(User $user, EditorialItem $item): bool
    {
        return $user->can($this->permissionType($item).'.view');
    }

    public function create(User $user): bool
    {
        return collect(array_keys(EditorialItem::TYPES))->contains(fn (string $type) => $user->can($type.'.create'));
    }

    public function update(User $user, EditorialItem $item): bool
    {
        return $user->can($this->permissionType($item).'.update');
    }

    public function delete(User $user, EditorialItem $item): bool
    {
        return $user->can($this->permissionType($item).'.delete');
    }

    public function restore(User $user, EditorialItem $item): bool
    {
        return $user->can($this->permissionType($item).'.restore');
    }

    public function publish(User $user): bool
    {
        return $user->can('news.publish');
    }

    private function permissionType(EditorialItem $item): string
    {
        return $item->type === 'event' ? 'event' : $item->type;
    }
}
