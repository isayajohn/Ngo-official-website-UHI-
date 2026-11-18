<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            'admin.access', 'finance.dashboard.view', 'finance.year.view', 'finance.year.manage',
            'finance.account.view', 'finance.account.manage', 'finance.bank.view',
            'finance.bank.manage', 'finance.cash.view', 'finance.cash.manage',
        ];
        foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
        $superAdmin = Role::query()->where('name', 'Super Admin')->where('guard_name', 'web')->first();
        $financeOfficer = Role::firstOrCreate(['name' => 'Finance Officer', 'guard_name' => 'web']);
        if ($financeOfficer->wasRecentlyCreated) $financeOfficer->syncPermissions($permissions);
        // Super Admin receives technical access. Finance approvals/execution are separate permissions
        // and are intentionally not granted by this foundation migration.
        if ($superAdmin) $superAdmin->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Preserve manually configured grants on rollback.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
