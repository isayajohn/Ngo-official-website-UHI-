<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\TeamMember;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Storage;

class InformationController extends Controller
{
    public function __invoke(string $path)
    {
        $page = config('ughi_pages.'.$path);
        abort_unless($page, 404);
        $content = in_array($path, ['our-team', 'our-history'], true) ? null : ContentPage::where('path', $path)->first();
        abort_if($content && ! $content->is_published, 404);

        $policyKeys = [
            'privacy-policy' => ['privacy_policy_title', 'privacy_policy_description', 'privacy_policy_body'],
            'safeguarding' => ['safeguarding_title', 'safeguarding_description', 'safeguarding_body'],
            'terms' => ['terms_title', 'terms_description', 'terms_body'],
        ];
        $policy = isset($policyKeys[$path]) ? app(SiteSettings::class) : null;
        [$titleKey, $descriptionKey, $bodyKey] = $policyKeys[$path] ?? [null, null, null];

        $kind = $page[4] ?? 'empty';
        $settings = app(SiteSettings::class);
        $leaders = $kind === 'leadership'
            ? TeamMember::published()->where('is_leader', true)->with(['department', 'position', 'leadershipChildren'])->get()
            : collect();
        $leadershipGroups = collect([
            'board' => 'Board Members',
            'senior_management' => 'Senior Management Team',
            'other' => 'Other Leadership',
        ])->map(fn (string $label, string $key): array => [
            'key' => $key,
            'label' => $label,
            'members' => $leaders->where('leadership_group', $key)->values(),
        ])->filter(fn (array $group): bool => $group['members']->isNotEmpty())->values();
        $history = (array) $settings->get('history_timeline', []);
        $isHistory = $kind === 'history';
        $isTeam = $kind === 'leadership';

        return view('pages.information', [
            'title' => ($policy && filled($policy->get($titleKey))) ? $policy->get($titleKey) : ($content?->translated('title') ?: ($isHistory ? $settings->get('history_title', $page[0]) : ($isTeam ? $settings->get('team_title', $page[0]) : $page[0]))),
            'description' => ($policy && filled($policy->get($descriptionKey))) ? $policy->get($descriptionKey) : ($content?->translated('description') ?: ($isHistory ? $settings->get('history_description', $page[1]) : ($isTeam ? $settings->get('team_description', $page[1]) : $page[1]))),
            'body' => ($policy && filled($policy->get($bodyKey))) ? $policy->get($bodyKey) : $content?->translated('body'),
            'eyebrow' => $content?->eyebrow ?: ($isHistory ? $settings->get('history_eyebrow', 'OUR JOURNEY') : ($isTeam ? $settings->get('team_eyebrow', 'OUR PEOPLE') : null)),
            'image' => $content?->hero_image ? Storage::disk('public')->url($content->hero_image) : null,
            'imageAlt' => $content?->hero_image_alt ?: $content?->translated('title'),
            'emptyTitle' => $page[2], 'emptyDescription' => $page[3],
            'kind' => $kind,
            'leaders' => $leaders,
            'leadershipGroups' => $leadershipGroups,
            'history' => $history,
        ]);
    }
}
