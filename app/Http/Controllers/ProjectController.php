<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectFilterRequest;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Region;

class ProjectController extends Controller
{
    public function index(ProjectFilterRequest $request)
    {
        $filters = $request->validated();
        $projects = Project::published()->with(['programme', 'region.country', 'district'])
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(fn ($q) => $q->where('title->en', 'like', '%'.$search.'%')->orWhere('title->sw', 'like', '%'.$search.'%')->orWhere('short_description->en', 'like', '%'.$search.'%')->orWhere('short_description->sw', 'like', '%'.$search.'%'));
            })
            ->when($filters['programme'] ?? null, fn ($q, $slug) => $q->whereHas('programme', fn ($p) => $p->where('slug', $slug)))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['region'] ?? null, fn ($q, $slug) => $q->whereHas('region', fn ($r) => $r->where('slug', $slug)))
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->whereBetween('start_date', [$year.'-01-01', $year.'-12-31']))
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('pages.projects', ['projects' => $projects, 'filters' => $filters, 'programmes' => Programme::published()->get(), 'regions' => Region::published()->get()]);
    }

    public function show(Project $project)
    {
        abort_unless(Project::published()->whereKey($project->id)->exists(), 404);
        $project->load(['programme', 'region.country', 'district', 'images', 'impactStatistics' => fn ($q) => $q->published()->orderBy('display_order')->limit(12)]);

        return view('pages.project', compact('project'));
    }
}
