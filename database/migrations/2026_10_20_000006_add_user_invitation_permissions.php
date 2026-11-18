<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'user.invite',
            'user.invitation.view',
            'user.invitation.resend',
            'user.invitation.revoke',
            'user.password_reset.send',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['Super Admin', 'Administrator'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permission assignments intact; they may have been customized.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
