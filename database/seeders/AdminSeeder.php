<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Development-only: never provision a known password in production.
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $admin = User::firstOrCreate(['email' => 'admin@ughi.local'], [
            'name' => 'UGHI Administrator', 'password' => 'Admin@1234',
            'must_change_password' => true, 'is_active' => true,
        ]);
        $admin->assignRole('Super Admin');
    }
}
