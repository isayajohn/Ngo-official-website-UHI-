<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();
        $defaults = [
            ['regions_covered', 'Regions covered', 'pin', 1],
            ['staff_members', 'Staff members', 'users', 2],
            ['health_facilities', 'Health facilities', 'hospital', 3],
        ];

        foreach ($defaults as [$metric, $label, $icon, $order]) {
            $exists = DB::table('impact_statistics')
                ->where('metric', $metric)
                ->whereNull('programme_id')
                ->whereNull('project_id')
                ->exists();

            if (! $exists) {
                DB::table('impact_statistics')->insert([
                    'programme_id' => null,
                    'project_id' => null,
                    'metric' => $metric,
                    'label' => json_encode(['en' => $label], JSON_THROW_ON_ERROR),
                    'value' => 0,
                    'suffix' => '',
                    'icon' => $icon,
                    'as_of_date' => null,
                    'verification_source' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'display_order' => $order,
                    'is_active' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('impact_statistics')
            ->where('metric', 'projects')
            ->whereNull('programme_id')
            ->whereNull('project_id')
            ->where('value', 0)
            ->where('is_active', false)
            ->whereNull('verified_at')
            ->where('display_order', 0)
            ->where('icon', 'users')
            ->update(['display_order' => 4, 'icon' => 'branch']);
    }

    public function down(): void
    {
        // Keep any homepage indicators admins may have edited after this migration.
    }
};
