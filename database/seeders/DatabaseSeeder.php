<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, AdminSeeder::class, ProgrammeSeeder::class, PhaseTwoSeeder::class, DepartmentPermissionSeeder::class, DepartmentSeeder::class, ContentManagementSeeder::class, ApprovalWorkflowSeeder::class, FinanceFoundationSeeder::class, PerformanceFoundationSeeder::class]);
        SiteSetting::firstOrCreate(['key' => 'global'], ['data' => config('ughi.defaults')]);
    }
}
