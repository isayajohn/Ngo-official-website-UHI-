<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = ['staff_performance.view', 'staff_performance.review', 'staff_performance.approve'];
        Role::query()->where('guard_name', 'web')->whereIn('name', ['Super Admin', 'Administrator'])->get()
            ->each(fn (Role $role) => $role->revokePermissionTo($permissions));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Appraisal access must be assigned explicitly by an authorized admin.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
