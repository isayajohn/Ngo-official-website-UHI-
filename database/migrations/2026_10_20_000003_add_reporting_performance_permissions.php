<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'report.view', 'report.create', 'report.update', 'report.submit', 'report.review', 'report.approve', 'report.return', 'report.reject', 'report.export', 'report.manage_types',
        'performance.view', 'performance.view_organization', 'performance.view_department', 'performance.view_programme', 'performance.view_project', 'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result', 'performance.manage_scale', 'performance.export',
        'staff_performance.view', 'staff_performance.review', 'staff_performance.approve',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($this->permissions as $permission) Permission::findOrCreate($permission, 'web');

        $all = Permission::query()->whereIn('name', $this->permissions)->where('guard_name', 'web')->get();
        $byName = fn (array $names) => $all->whereIn('name', $names);
        Role::query()->where('guard_name', 'web')->where('name', 'Super Admin')->first()?->givePermissionTo($byName(array_values(array_filter($this->permissions, fn ($name) => ! str_starts_with($name, 'staff_performance.')))));
        Role::query()->where('guard_name', 'web')->where('name', 'Administrator')->first()?->givePermissionTo($byName(array_values(array_filter($this->permissions, fn ($name) => ! str_starts_with($name, 'staff_performance.')))));
        Role::query()->where('guard_name', 'web')->where('name', 'M&E Officer')->first()?->givePermissionTo($byName([
            'admin.access', 'performance.view', 'performance.view_department', 'performance.view_programme', 'performance.view_project',
            'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result', 'performance.export',
            'report.view', 'report.create', 'report.submit', 'report.review', 'report.export',
        ]));
        Role::query()->where('guard_name', 'web')->where('name', 'Executive Secretary')->first()?->givePermissionTo($byName(['performance.view', 'performance.view_organization', 'report.view', 'report.export']));
        Role::query()->where('guard_name', 'web')->where('name', 'Programme Officer')->first()?->givePermissionTo($byName(['performance.view', 'performance.view_programme', 'performance.view_project', 'report.view', 'report.create', 'report.submit']));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Preserve any grants configured by administrators after deployment.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
