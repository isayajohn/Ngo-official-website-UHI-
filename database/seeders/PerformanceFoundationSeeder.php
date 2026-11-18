<?php

namespace Database\Seeders;

use App\Models\PerformanceRatingScale;
use Illuminate\Database\Seeder;

class PerformanceFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $bands = [
            [90, 100, 'Excellent', 'Starter configuration. Edit these thresholds to match UGHI policy.', 1],
            [80, 89.99, 'Very Good', 'Starter configuration. Edit these thresholds to match UGHI policy.', 2],
            [70, 79.99, 'Good', 'Starter configuration. Edit these thresholds to match UGHI policy.', 3],
            [60, 69.99, 'Needs Attention', 'Starter configuration. Edit these thresholds to match UGHI policy.', 4],
            [0, 59.99, 'Critical', 'Starter configuration. Edit these thresholds to match UGHI policy.', 5],
        ];

        foreach ($bands as [$minimum, $maximum, $label, $description, $order]) {
            PerformanceRatingScale::firstOrCreate(
                ['name' => 'UGHI Performance Rating', 'label' => $label],
                ['minimum_score' => $minimum, 'maximum_score' => $maximum, 'description' => $description, 'display_order' => $order, 'active' => true],
            );
        }
    }
}
