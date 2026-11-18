<?php

namespace Database\Seeders;

use App\Models\Programme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = [
            ['Maternal, Newborn, Child and Adolescent Health', 'Supporting healthier lives at every stage, from pregnancy and birth through childhood and adolescence.', 'heart'],
            ['HIV Prevention, Care, Treatment and Client Retention', 'Community-led prevention, access to care and sustained support for people living with HIV.', 'shield'],
            ['Cancer Prevention, Screening and Non-Communicable Diseases', 'Building awareness and strengthening access to prevention, early detection and continuing care.', 'activity'],
            ['Youth Sexual and Reproductive Health, Rights and Empowerment', 'Supporting young people with knowledge, choice and opportunities to lead.', 'users'],
            ['Mental Health and Psychosocial Support', 'Promoting mental well-being, reducing stigma and supporting compassionate community care.', 'sun'],
            ['Nutrition, Water, Sanitation, Hygiene and Environmental Health', 'Connecting healthy nutrition and safe environments with healthier communities.', 'droplet'],
            ['Health Systems Strengthening, Capacity Building and Research', 'Strengthening people, systems and evidence for equitable, sustainable health services.', 'book'],
        ];
        foreach ($programmes as $order => [$title, $description, $icon]) {
            Programme::withTrashed()->firstOrCreate(['slug' => Str::slug($title)], [
                'title' => ['en' => $title], 'short_description' => ['en' => $description],
                'description' => ['en' => $description], 'icon' => $icon,
                'display_order' => $order + 1, 'is_active' => true,
            ]);
        }
    }
}
