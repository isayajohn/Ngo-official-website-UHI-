<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::withTrashed()->firstOrCreate(['code' => 'TZ'], ['name' => 'Tanzania', 'is_active' => true]);
        $region = Region::withTrashed()->firstOrCreate(['slug' => 'ruvuma'], ['country_id' => $country->id, 'name' => 'Ruvuma', 'is_active' => true]);
        District::withTrashed()->firstOrCreate(['slug' => 'namtumbo'], ['region_id' => $region->id, 'name' => 'Namtumbo', 'is_active' => true]);
    }
}
