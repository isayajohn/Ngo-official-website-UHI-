<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'mel.dashboard.view',
        'mel.results_framework.view', 'mel.results_framework.manage',
        'mel.indicator.view', 'mel.indicator.create', 'mel.indicator.update', 'mel.indicator.delete',
        'mel.target.view', 'mel.target.manage',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissionModels = collect($this->permissions)->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $rolePermissions = [
            'Super Admin' => $this->permissions,
            'M&E Officer' => ['mel.dashboard.view', 'mel.results_framework.view', 'mel.results_framework.manage', 'mel.indicator.view', 'mel.indicator.create', 'mel.indicator.update', 'mel.target.view', 'mel.target.manage'],
            'Programme Officer' => ['mel.dashboard.view', 'mel.results_framework.view', 'mel.indicator.view', 'mel.target.view'],
            'Executive Secretary' => ['mel.dashboard.view', 'mel.results_framework.view', 'mel.indicator.view', 'mel.target.view'],
        ];

        foreach ($rolePermissions as $roleName => $names) {
            Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permissionModels->whereIn('name', $names)->all());
        }

        $administrator = Role::query()->where('name', 'Administrator')->where('guard_name', 'web')->first();
        if ($administrator) foreach ($permissionModels as $permission) $administrator->revokePermissionTo($permission);
        if ($administrator) {
            Permission::query()->where('guard_name', 'web')->whereIn('name', ['performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result'])->each(fn (Permission $permission) => $administrator->revokePermissionTo($permission));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($this->permissions as $name) Permission::query()->where('name', $name)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
