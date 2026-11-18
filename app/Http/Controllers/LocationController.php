<?php

namespace App\Http\Controllers;

use App\Models\Country;

class LocationController extends Controller
{
    public function __invoke()
    {
        $countries = Country::published()->with(['regions' => fn ($q) => $q->published()->with(['districts' => fn ($d) => $d->published()])->withCount(['projects' => fn ($p) => $p->published()])])->paginate(12);

        return view('pages.locations', compact('countries'));
    }
}
