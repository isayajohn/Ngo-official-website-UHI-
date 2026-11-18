<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $grants = [
        'Content Manager' => [
            'news.view', 'news.create', 'news.update', 'news.delete',
            'event.view', 'event.create', 'event.update', 'event.delete',
            'publication.view', 'publication.create', 'publication.update', 'publication.delete',
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
        ],
        'Communications Officer' => [
            'event.view', 'event.create', 'event.update',
            'publication.view', 'publication.create', 'publication.update',
            'gallery.view', 'gallery.create', 'gallery.update',
            'partnership.view', 'partnership.update',
        ],
        'Membership Officer' => ['volunteer.view', 'volunteer.update'],
    ];

    public function up(): void
    {
        foreach ($this->grants as $roleName => $permissions) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if (! $role) continue;

            foreach ($permissions as $permissionName) {
                $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Preserve role grants that administrators may have customized after this migration.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
