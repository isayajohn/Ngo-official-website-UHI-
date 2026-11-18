<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Country;
use App\Models\District;
use App\Models\EditorialItem;
use App\Models\ImpactStatistic;
use App\Models\MediaAsset;
use App\Models\Partner;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Region;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoContentSeeder extends Seeder
{
    private const DEMO_SOURCE = 'DEMO ONLY: illustrative sample data for local preview; not verified organizational results.';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DemoContentSeeder is restricted to local and testing environments.');
        }

        $this->installDemoImages();
        $regions = $this->createDemoLocations();
        $this->createDemoProjects($regions);
        $this->installPartnerPlaceholderLogos();
        $this->createDemoPartners();
        $this->createDemoStories();
        $this->createDemoLeaders();
        $this->createDemoHistory();
        $this->publishDemoStatistics();
        $this->registerDemoMedia();
    }

    private function installDemoImages(): void
    {
        foreach (['community-care.jpg', 'listening-care.jpg'] as $image) {
            $source = public_path('images/'.$image);
            $destination = 'demo-content/'.$image;

            if (is_file($source) && ! Storage::disk('public')->exists($destination)) {
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
    }

    /** @return array<string, Region> */
    private function createDemoLocations(): array
    {
        $country = Country::query()->where('code', 'TZ')->firstOrFail();
        $locations = [
            'ruvuma' => ['Ruvuma', 'Namtumbo'],
            'dar-es-salaam' => ['Dar es Salaam', 'Ilala'],
            'dodoma' => ['Dodoma', 'Dodoma Urban'],
            'mwanza' => ['Mwanza', 'Ilemela'],
        ];
        $regions = [];

        foreach ($locations as $slug => [$name, $districtName]) {
            $region = Region::withTrashed()->firstOrCreate(['slug' => $slug], [
                'country_id' => $country->id,
                'name' => $name,
                'description' => ['en' => 'Demo location for local preview only; replace with verified UHI service areas before production.'],
                'display_order' => count($regions) + 1,
                'is_active' => true,
            ]);
            if ($region->trashed()) {
                $region->restore();
            }

            $district = District::withTrashed()->firstOrCreate(['slug' => str($districtName)->slug()], [
                'region_id' => $region->id,
                'name' => $districtName,
                'display_order' => 1,
                'is_active' => true,
            ]);
            if ($district->trashed()) {
                $district->restore();
            }

            $regions[$slug] = $region;
        }

        return $regions;
    }

    /** @param array<string, Region> $regions */
    private function createDemoProjects(array $regions): void
    {
        $items = [
            ['demo-community-health-outreach', 'maternal-newborn-child-and-adolescent-health', 'ruvuma', 'Community health outreach in Namtumbo', 'Local teams connect families with maternal, newborn and child health information and services. This illustrative demo project is for preview only.', 'demo-content/community-care.jpg', true, 210],
            ['demo-youth-wellbeing-dialogues', 'youth-sexual-and-reproductive-health-rights-and-empowerment', 'dar-es-salaam', 'Youth wellbeing dialogues', 'Youth-led sessions create welcoming spaces for health information, peer learning and community participation. This illustrative demo project is for preview only.', 'demo-content/listening-care.jpg', true, 145],
            ['demo-health-systems-learning', 'health-systems-strengthening-capacity-building-and-research', 'dodoma', 'Health systems learning and quality improvement', 'A sample partnership supports practical learning, service quality and evidence use across local health teams. This illustrative demo project is for preview only.', 'demo-content/community-care.jpg', true, 175],
            ['demo-community-research', 'hiv-prevention-care-treatment-and-client-retention', 'mwanza', 'Community research and service access', 'A sample community collaboration explores ways to improve access to respectful, people-centred health services. This illustrative demo project is for preview only.', 'demo-content/listening-care.jpg', false, 90],
        ];

        foreach ($items as [$slug, $programmeSlug, $regionSlug, $title, $description, $image, $featured, $reach]) {
            $programme = Programme::query()->where('slug', $programmeSlug)->first();
            if (! $programme) {
                continue;
            }

            $project = Project::withTrashed()->firstOrCreate(['slug' => $slug], [
                'programme_id' => $programme->id,
                'region_id' => $regions[$regionSlug]->id,
                'district_id' => District::query()->where('region_id', $regions[$regionSlug]->id)->value('id'),
                'code' => strtoupper($slug),
                'title' => ['en' => 'Demo: '.$title],
                'short_description' => ['en' => $description],
                'description' => ['en' => $description."\n\nAll project details and figures on this page are illustrative demo content for local preview."],
                'challenge' => ['en' => 'Demo summary: describe the local health access challenge being addressed.'],
                'objectives' => ['en' => 'Demo objective: show the project objective and intended change.'],
                'activities' => ['en' => 'Demo activities: community engagement, service coordination and learning.'],
                'target_groups' => ['en' => 'Illustrative target groups for local preview.'],
                'featured_image' => Storage::disk('public')->exists($image) ? $image : null,
                'image_alt' => 'Illustrative community health activity for demo preview',
                'start_date' => today()->subMonths(4),
                'end_date' => today()->addMonths(8),
                'status' => ProjectStatus::Active,
                'location' => 'Demo location · '.$regions[$regionSlug]->name,
                'funding_partner' => 'Demo partnership',
                'people_reached' => $reach,
                'is_featured' => $featured,
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ]);

            if ($project->trashed()) {
                $project->restore();
            }

            $this->createDemoStatistic('people_reached', $project->programme_id, $project->id, 'People reached · Demo', $reach, 'users', 1);
        }
    }

    private function createDemoPartners(): void
    {
        $partners = [
            ['demo-community-health-alliance', 'Demo: Community Health Alliance', 'Community organization', 'A sample community partnership profile for local website preview.', 'demo-content/community-care.jpg', 'CHA'],
            ['demo-youth-wellbeing-network', 'Demo: Youth Wellbeing Network', 'Youth-led organization', 'A sample youth-led partnership profile for local website preview.', 'demo-content/listening-care.jpg', 'YWN'],
            ['demo-health-research-collaborative', 'Demo: Health Research Collaborative', 'Research partner', 'A sample research partnership profile for local website preview.', 'demo-content/community-care.jpg', 'HRC'],
            ['demo-district-health-team', 'Demo: District Health Team', 'Health system partner', 'A sample health-system partnership profile for local website preview.', 'demo-content/listening-care.jpg', 'DHT'],
            ['demo-community-care-group', 'Demo: Community Care Group', 'Community organization', 'A sample community organization profile for local website preview.', 'demo-content/community-care.jpg', 'CCG'],
            ['demo-digital-health-lab', 'Demo: Digital Health Lab', 'Innovation partner', 'A sample digital-health partnership profile for local website preview.', 'demo-content/listening-care.jpg', 'DHL'],
        ];

        foreach ($partners as $order => [$slug, $name, $type, $description, $image, $mark]) {
            $logo = 'demo-content/partners/'.$slug.'.svg';
            $partner = Partner::withTrashed()->firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'partner_type' => $type,
                'short_description' => $description,
                'description' => $description.' All details are demo content and do not describe a confirmed partnership.',
                'logo' => $logo,
                'logo_alt' => 'Illustrative demo logo for '.$name,
                'featured_image' => Storage::disk('public')->exists($image) ? $image : null,
                'image_alt' => 'Illustrative community health activity for demo preview',
                'is_featured' => true,
                'show_on_website' => true,
                'active' => true,
                'display_order' => $order + 1,
            ]);
            if ($partner->trashed()) {
                $partner->restore();
            }
            if (blank($partner->logo) || str_starts_with($partner->logo, 'demo-content/partners/')) {
                $partner->forceFill(['logo' => $logo, 'logo_alt' => 'Illustrative demo logo for '.$name])->saveQuietly();
            }
        }
    }

    private function installPartnerPlaceholderLogos(): void
    {
        $logos = [
            'demo-community-health-alliance' => 'CHA',
            'demo-youth-wellbeing-network' => 'YWN',
            'demo-health-research-collaborative' => 'HRC',
            'demo-district-health-team' => 'DHT',
            'demo-community-care-group' => 'CCG',
            'demo-digital-health-lab' => 'DHL',
        ];

        foreach ($logos as $slug => $mark) {
            $path = 'demo-content/partners/'.$slug.'.svg';
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 120" role="img"><rect x="4" y="4" width="312" height="112" rx="18" fill="#fff"/><circle cx="68" cy="60" r="36" fill="#0870bd"/><text x="68" y="67" text-anchor="middle" font-family="Arial,sans-serif" font-size="19" font-weight="700" fill="#fff">'.$mark.'</text><text x="120" y="55" font-family="Arial,sans-serif" font-size="18" font-weight="700" fill="#06477f">Demo Partner</text><text x="120" y="79" font-family="Arial,sans-serif" font-size="12" letter-spacing="2" fill="#596773">SAMPLE LOGO</text></svg>';
            Storage::disk('public')->put($path, $svg);
        }
    }

    private function createDemoStories(): void
    {
        $published = now()->subDays(2);
        $stories = [
            ['news', 'demo-community-health-outreach-story', 'Demo: Community health outreach brings services closer', 'A sample news story for local preview. Replace this with a verified update before launch.', 'demo-content/community-care.jpg', null, null, null],
            ['news', 'demo-youth-wellbeing-story', 'Demo: Young people lead a wellbeing dialogue', 'A sample community story for local preview. Replace this with an approved story before launch.', 'demo-content/listening-care.jpg', null, null, null],
            ['news', 'demo-health-learning-story', 'Demo: Health teams share learning across districts', 'A sample learning update for local preview. Replace with verified results before launch.', 'demo-content/community-care.jpg', null, null, null],
            ['event', 'demo-community-health-open-day', 'Demo: Community health open day', 'Sample event listing for local preview. Confirm the organizer and details before publishing to a live website.', 'demo-content/community-care.jpg', now()->addWeeks(2), now()->addWeeks(2)->addHours(4), 'Demo venue · Tanzania'],
            ['event', 'demo-youth-health-forum', 'Demo: Youth health and wellbeing forum', 'Sample event listing for local preview. Confirm the organizer and details before publishing to a live website.', 'demo-content/listening-care.jpg', now()->addWeeks(5), now()->addWeeks(5)->addHours(5), 'Demo venue · Tanzania'],
            ['publication', 'demo-community-health-learning-brief', 'Demo: Community health learning brief', 'An illustrative publication record for local preview only.', 'demo-content/community-care.jpg', null, null, null],
            ['gallery', 'demo-community-health-gallery', 'Demo: Community health activities', 'Illustrative preview image. Replace with approved UHI photography before launch.', 'demo-content/community-care.jpg', null, null, null],
            ['gallery', 'demo-youth-health-gallery', 'Demo: Youth health dialogue', 'Illustrative preview image. Replace with approved UHI photography before launch.', 'demo-content/listening-care.jpg', null, null, null],
        ];

        foreach ($stories as [$type, $slug, $title, $summary, $image, $starts, $ends, $location]) {
            $attributes = [
                'title' => $title,
                'summary' => $summary,
                'body' => $summary."\n\nThis content is sample data for local preview. Replace it with reviewed, accurate information before production use.",
                'image' => Storage::disk('public')->exists($image) ? $image : null,
                'image_alt' => 'Illustrative demo image; replace with approved UHI photography',
                'event_starts_at' => $starts,
                'event_ends_at' => $ends,
                'event_location' => $location,
                'external_url' => $type === 'publication' ? 'https://example.org/demo-publication' : null,
                'document_label' => $type === 'publication' ? 'Example publication link' : null,
                'is_published' => true,
                'published_at' => $published,
            ];
            $item = EditorialItem::withTrashed()->firstOrCreate(['slug' => $slug], ['type' => $type] + $attributes);
            if ($item->trashed()) {
                $item->restore();
            }
        }
    }

    private function createDemoLeaders(): void
    {
        $department = \App\Models\Department::query()->where('code', 'SEC')->first();
        $leaders = [
            ['demo-executive-director', 'Sample Chief Executive Officer (Demo)', 'Chief Executive Officer', 'senior_management', null, 'demo-content/community-care.jpg'],
            ['demo-programme-lead', 'Sample Director of Programmes (Demo)', 'Director of Programmes', 'senior_management', 'demo-executive-director', 'demo-content/listening-care.jpg'],
            ['demo-community-representative', 'Sample Board Chairperson (Demo)', 'Board Chairperson', 'board', null, 'demo-content/community-care.jpg'],
            ['demo-board-member-one', 'Sample Board Member One (Demo)', 'Board Member', 'board', 'demo-community-representative', 'demo-content/listening-care.jpg'],
            ['demo-board-member-two', 'Sample Board Member Two (Demo)', 'Board Member', 'board', 'demo-community-representative', 'demo-content/community-care.jpg'],
        ];

        foreach ($leaders as $order => [$slug, $name, $role, $group, $parentSlug, $photo]) {
            $leader = TeamMember::withTrashed()->updateOrCreate(['slug' => $slug], [
                'department_id' => $department?->id,
                'name' => $name,
                'leadership_role' => $role,
                'leadership_group' => $group,
                'leadership_parent_id' => null,
                'short_bio' => 'Illustrative demo profile for local preview only. Replace this with an approved biography before launch.',
                'bio' => 'This is sample content for previewing the public team page. It is not an official UGHI profile.',
                'photo' => Storage::disk('public')->exists($photo) ? $photo : null,
                'photo_alt' => 'Illustrative demo profile image',
                'display_order' => $order + 1,
                'show_on_website' => true,
                'active' => true,
                'is_leader' => true,
            ]);
            if ($leader->trashed()) {
                $leader->restore();
            }
        }

        foreach ($leaders as [$slug, , , , $parentSlug]) {
            if ($parentSlug) {
                TeamMember::where('slug', $slug)->update(['leadership_parent_id' => TeamMember::where('slug', $parentSlug)->value('id')]);
            }
        }
    }

    private function createDemoHistory(): void
    {
        $setting = SiteSetting::query()->where('key', 'global')->first();
        if (! $setting) return;

        $data = $setting->data ?? [];
        if (! empty($data['history_timeline'])) return;

        $data['history_timeline'] = [
            ['year' => 'Demo year 1', 'title' => 'Demo milestone: a community initiative begins', 'description' => 'Illustrative placeholder for the organization’s founding story. Replace with a verified milestone before launch.'],
            ['year' => 'Demo year 2', 'title' => 'Demo milestone: local partnerships grow', 'description' => 'Illustrative placeholder showing how timeline entries appear. Replace with an approved account of UGHI’s work.'],
            ['year' => 'Present · Demo', 'title' => 'Demo milestone: learning continues', 'description' => 'Sample timeline content for local preview only. Add verified dates and details in the page management controls.'],
        ];
        $setting->update(['data' => $data]);
    }

    private function publishDemoStatistics(): void
    {
        $adminId = User::query()->where('email', 'admin@ughi.local')->value('id');
        $items = [
            ['regions_covered', 'Regions covered', 4, '+', 'pin', 1],
            ['staff_members', 'Staff members', 18, '+', 'users', 2],
            ['health_facilities', 'Health facilities', 42, '+', 'hospital', 3],
            ['projects', 'Projects', 3, '+', 'branch', 4],
            ['people_reached', 'People reached', 1250, '+', 'users', 5],
        ];

        foreach ($items as [$metric, $label, $value, $suffix, $icon, $order]) {
            $statistic = ImpactStatistic::withTrashed()->firstOrCreate(
                ['metric' => $metric, 'programme_id' => null, 'project_id' => null],
                ['label' => ['en' => $label], 'value' => 0, 'is_active' => false],
            );

            $isUntouchedStarter = $statistic->value === 0 && ! $statistic->is_active && ! $statistic->verified_at && blank($statistic->verification_source);
            $isDemoRecord = str_starts_with((string) $statistic->verification_source, 'DEMO ONLY:');
            if (! $isUntouchedStarter && ! $isDemoRecord) {
                continue;
            }

            if ($statistic->trashed()) {
                $statistic->restore();
            }
            $statistic->forceFill([
                'label' => ['en' => $label.' (Demo)'],
                'value' => $value,
                'suffix' => $suffix,
                'icon' => $icon,
                'as_of_date' => today(),
                'verification_source' => self::DEMO_SOURCE,
                'verified_at' => now(),
                'verified_by' => $adminId,
                'display_order' => $order,
                'is_active' => true,
            ])->saveQuietly();
        }
    }

    private function createDemoStatistic(string $metric, int $programmeId, int $projectId, string $label, int $value, string $icon, int $order): void
    {
        $statistic = ImpactStatistic::withTrashed()->firstOrCreate(
            ['metric' => $metric, 'programme_id' => $programmeId, 'project_id' => $projectId],
            ['label' => ['en' => $label], 'value' => $value, 'icon' => $icon, 'display_order' => $order, 'is_active' => false],
        );

        if ($statistic->trashed()) {
            $statistic->restore();
        }
        if (! $statistic->verified_at && ! $statistic->is_active) {
            $statistic->forceFill([
                'label' => ['en' => $label],
                'value' => $value,
                'suffix' => '+',
                'icon' => $icon,
                'as_of_date' => today(),
                'verification_source' => self::DEMO_SOURCE,
                'verified_at' => now(),
                'verified_by' => User::query()->where('email', 'admin@ughi.local')->value('id'),
                'display_order' => $order,
                'is_active' => true,
            ])->saveQuietly();
        }
    }

    private function registerDemoMedia(): void
    {
        foreach ([
            ['community-care.jpg', 'Community health demo image', 'demo-content/community-care.jpg', 'Community care demonstration image.'],
            ['listening-care.jpg', 'Community listening demo image', 'demo-content/listening-care.jpg', 'Community listening demonstration image.'],
        ] as [$filename, $name, $path, $alt]) {
            if (Storage::disk('public')->exists($path)) {
                MediaAsset::firstOrCreate(['path' => $path], [
                    'name' => $name,
                    'alt_text' => $alt,
                    'caption' => 'Demo-only media for local preview. Replace with approved UHI photography.',
                    'collection' => 'demo',
                    'is_public' => true,
                ]);
            }
        }
    }
}
