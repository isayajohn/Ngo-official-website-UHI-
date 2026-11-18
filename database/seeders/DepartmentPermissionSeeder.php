<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DepartmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            'department.view', 'department.create', 'department.update', 'department.delete', 'department.restore',
            'position.view', 'position.create', 'position.update', 'position.delete', 'position.restore',
            'department.assign_programme', 'department.assign_project', 'department.manage_staff',
        ];
        foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
        Role::findByName('Super Admin', 'web')->givePermissionTo($permissions);
        Role::findByName('Administrator', 'web')->givePermissionTo($permissions);
        Role::findByName('Programme Officer', 'web')->givePermissionTo(['department.view', 'position.view', 'department.assign_programme', 'department.assign_project']);
        Role::findByName('M&E Officer', 'web')->givePermissionTo(['department.view', 'position.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
