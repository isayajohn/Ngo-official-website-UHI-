<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ContentManagementSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('ughi_pages', []) as $path => $page) {
            $record = ContentPage::withTrashed()->firstOrCreate(['path' => $path], [
                'eyebrow' => 'USTAWI GLOBAL HEALTH INITIATIVE',
                'title' => ['en' => $page[0]],
                'description' => ['en' => $page[1]],
                'body' => ['en' => ''],
                'is_published' => true,
                'display_order' => 0,
            ]);
            if ($record->trashed()) $record->restore();
        }

        $permissions = collect(['view', 'create', 'update', 'delete', 'restore'])->map(fn ($action) => "media.$action")->all();
        foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
        foreach (['Super Admin', 'Administrator'] as $role) Role::findByName($role, 'web')->givePermissionTo($permissions);
        Role::findByName('Content Manager', 'web')->givePermissionTo(['page.view', 'page.create', 'page.update', 'partner.view', 'partner.create', 'partner.update', 'media.view', 'media.create', 'media.update']);
        Role::findByName('Communications Officer', 'web')->givePermissionTo(['page.view', 'page.update', 'partner.view', 'partner.create', 'partner.update', 'media.view', 'media.create', 'media.update']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
