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
        $content = ContentPage::where('path', $path)->first();
        abort_if($content && ! $content->is_published, 404);

        $policyKeys = [
            'privacy-policy' => ['privacy_policy_title', 'privacy_policy_description', 'privacy_policy_body'],
            'safeguarding' => ['safeguarding_title', 'safeguarding_description', 'safeguarding_body'],
            'terms' => ['terms_title', 'terms_description', 'terms_body'],
        ];
        $policy = isset($policyKeys[$path]) ? app(SiteSettings::class) : null;
        [$titleKey, $descriptionKey, $bodyKey] = $policyKeys[$path] ?? [null, null, null];

        $leaders = ($page[4] ?? null) === 'leadership'
            ? TeamMember::published()->where('is_leader', true)->with(['department', 'position'])->get()
            : collect();

        return view('pages.information', [
            'title' => ($policy && filled($policy->get($titleKey))) ? $policy->get($titleKey) : ($content?->translated('title') ?: $page[0]),
            'description' => ($policy && filled($policy->get($descriptionKey))) ? $policy->get($descriptionKey) : ($content?->translated('description') ?: $page[1]),
            'body' => ($policy && filled($policy->get($bodyKey))) ? $policy->get($bodyKey) : $content?->translated('body'),
            'eyebrow' => $content?->eyebrow,
            'image' => $content?->hero_image ? Storage::disk('public')->url($content->hero_image) : null,
            'imageAlt' => $content?->hero_image_alt ?: $content?->translated('title'),
            'emptyTitle' => $page[2], 'emptyDescription' => $page[3],
            'kind' => $page[4] ?? 'empty',
            'leaders' => $leaders,
        ]);
    }
}
