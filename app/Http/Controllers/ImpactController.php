<?php

namespace App\Http\Controllers;

use App\Models\ImpactStatistic;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImpactController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate(['programme' => ['nullable', 'string', 'max:255', Rule::exists('programmes', 'slug')], 'metric' => ['nullable', Rule::in(array_keys(ImpactStatistic::METRICS))]]);
        $statistics = ImpactStatistic::published()->with(['programme', 'project'])
            ->when($filters['programme'] ?? null, fn ($q, $slug) => $q->whereHas('programme', fn ($p) => $p->where('slug', $slug)))
            ->when($filters['metric'] ?? null, fn ($q, $metric) => $q->where('metric', $metric))
            ->orderByDesc('as_of_date')->orderBy('display_order')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('pages.impact', ['statistics' => $statistics, 'programmes' => Programme::published()->get(), 'filters' => $filters]);
    }
}
