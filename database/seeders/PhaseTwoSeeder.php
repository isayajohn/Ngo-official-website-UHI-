<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PhaseTwoSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['project.publish', 'impact.verify', 'impact.publish'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (['Super Admin', 'Administrator'] as $name) {
            Role::findByName($name, 'web')->givePermissionTo(['project.publish', 'impact.verify', 'impact.publish']);
        }
        Role::findByName('M&E Officer', 'web')->givePermissionTo(['impact.verify']);
        Role::findByName('Content Manager', 'web')->givePermissionTo(['project.view', 'project.create', 'project.update']);
        $settings = SiteSetting::where('key', 'global')->first();
        if ($settings && ($settings->data['location_description'] ?? null) === 'Our initial focus is Namtumbo District in Tanzania’s Ruvuma Region. We believe sustainable health begins with listening to the people who know their communities best.') {
            $settings->update(['data' => array_replace($settings->data, ['location_description' => config('ughi.defaults.location_description')])]);
        }
        $this->call([LocationSeeder::class, ImpactStatisticSeeder::class]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
