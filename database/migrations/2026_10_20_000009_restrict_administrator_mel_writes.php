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
        $administrator = Role::query()->where('name', 'Administrator')->where('guard_name', 'web')->first();
        if ($administrator) {
            Permission::query()->where('guard_name', 'web')->whereIn('name', [
                'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result',
            ])->each(fn (Permission $permission) => $administrator->revokePermissionTo($permission));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
