<?php

namespace Database\Seeders;

use App\Models\ImpactStatistic;
use Illuminate\Database\Seeder;

class ImpactStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $icons = [
            'regions_covered' => 'pin',
            'staff_members' => 'users',
            'health_facilities' => 'hospital',
            'projects' => 'branch',
        ];
        $orders = ['regions_covered' => 1, 'staff_members' => 2, 'health_facilities' => 3, 'projects' => 4];

        foreach (ImpactStatistic::METRICS as $index => $label) {
            if ($index === 'other') {
                continue;
            }
            $statistic = ImpactStatistic::withTrashed()->firstOrCreate(
                ['metric' => $index, 'programme_id' => null, 'project_id' => null],
                ['label' => ['en' => $label], 'value' => 0, 'icon' => $icons[$index] ?? 'users', 'display_order' => $orders[$index] ?? 0, 'is_active' => false],
            );

            // Preserve any edited/published figures; only repair the untouched legacy seed row.
            if ($index === 'projects' && $statistic->value === 0 && ! $statistic->is_active && ! $statistic->verified_at && $statistic->icon === 'users' && $statistic->display_order === 0) {
                $statistic->forceFill(['icon' => $icons[$index], 'display_order' => $orders[$index]])->saveQuietly();
            }
        }
    }
}
