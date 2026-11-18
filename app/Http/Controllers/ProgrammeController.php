<?php

namespace App\Http\Controllers;

use App\Models\Programme;

class ProgrammeController extends Controller
{
    public function index()
    {
        return view('pages.programmes', ['programmes' => Programme::published()->paginate(9)]);
    }

    public function show(Programme $programme)
    {
        abort_unless($programme->is_active, 404);

        $programme->load(['impactStatistics' => fn ($q) => $q->published()->whereNull('project_id')->latest('as_of_date')->limit(12)]);
        $projects = $programme->projects()->published()->with(['programme', 'region.country', 'district'])->latest('published_at')->paginate(6);

        return view('pages.programme', compact('programme', 'projects'));
    }
}
