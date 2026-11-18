<?php

namespace App\Http\Controllers;

use App\Models\Partner;

class PartnerController extends Controller
{
    public function index()
    {
        return view('pages.partners', ['partners' => Partner::published()->get()]);
    }

    public function show(Partner $partner)
    {
        abort_unless($partner->active && $partner->show_on_website, 404);
        return view('pages.partner', compact('partner'));
    }
}
