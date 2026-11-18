<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ImpactStatistic;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Partner;
use App\Support\SiteSettings;

class HomeController extends Controller
{
    public function __invoke(SiteSettings $site)
    {
        $selected = $site->get('featured_project_id');
        $featuredProject = $selected ? Project::published()->whereKey($selected)->with(['programme', 'region.country', 'district', 'impactStatistics' => fn ($q) => $q->published()->where('metric', 'people_reached')->latest('as_of_date')->limit(1)])->first() : null;
        $featuredProject ??= Project::published()->where('is_featured', true)->with(['programme', 'region.country', 'district', 'impactStatistics' => fn ($q) => $q->published()->where('metric', 'people_reached')->latest('as_of_date')->limit(1)])->latest('published_at')->first();
        $featuredProjects = Project::published()->where('is_featured', true)->when($featuredProject, fn ($q) => $q->where('id', '!=', $featuredProject->id))->with(['programme', 'region.country', 'district'])->latest('published_at')->limit(3)->get();
        $impactStatistics = ImpactStatistic::published()->whereNull('programme_id')->whereNull('project_id')->latest('as_of_date')->orderByDesc('id')->limit(100)->get()->unique(fn ($stat) => $stat->metric === 'other' ? 'other:'.($stat->label['en'] ?? $stat->id) : $stat->metric)->sortBy('display_order')->take(5);
        $countries = Country::published()->with(['regions' => fn ($q) => $q->published()->with(['districts' => fn ($d) => $d->published()])])->limit(6)->get();

        $partners = Partner::published()->where('is_featured', true)->limit(6)->get();
        return view('pages.home', ['programmes' => Programme::published()->get(), 'featuredProject' => $featuredProject, 'featuredProjects' => $featuredProjects, 'impactStatistics' => $impactStatistics, 'countries' => $countries, 'partners' => $partners]);
    }
}
