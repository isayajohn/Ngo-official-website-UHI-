<?php

namespace Database\Seeders;

use App\Models\ImpactStatistic;
use Illuminate\Database\Seeder;

class ImpactStatisticSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ImpactStatistic::METRICS as $index => $label) {
            if ($index === 'other') {
                continue;
            }
            ImpactStatistic::withTrashed()->firstOrCreate(['metric' => $index, 'programme_id' => null, 'project_id' => null], ['label' => ['en' => $label], 'value' => 0, 'is_active' => false]);
        }
    }
}
