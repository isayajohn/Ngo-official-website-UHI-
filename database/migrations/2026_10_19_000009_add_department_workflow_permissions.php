<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = ['organization.full_access', 'department.manage', 'department.manage_leadership', 'department.assign_task', 'department.view_reports', 'department.manage_projects', 'department.manage_programmes', 'department.approve', 'department.view_budget', 'department.manage_documents', 'department.manage_messages', 'department.view_activity'];
        foreach ($permissions as $name) Permission::findOrCreate($name, 'web');
        foreach (['Super Admin', 'Administrator'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) $role->givePermissionTo(['organization.full_access', ...array_slice($permissions, 1)]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep grants and permission rows so a rollback cannot revoke live access configuration.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
