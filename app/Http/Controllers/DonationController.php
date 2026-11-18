<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    public function index()
    {
        return view('pages.donate', ['programmes' => Programme::published()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'programme_id' => ['nullable', 'integer', Rule::exists('programmes', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'amount' => ['required', 'numeric', 'min:1', 'max:9999999999.99'],
            'currency' => ['required', Rule::in(['TZS', 'USD'])],
            'frequency' => ['required', Rule::in(array_keys(Donation::FREQUENCIES))],
            'purpose' => ['required', Rule::in(array_keys(Donation::PURPOSES))],
            'payment_method' => ['required', Rule::in(array_keys(Donation::PAYMENT_METHODS))],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);

        unset($validated['privacy_consent'], $validated['website']);
        Donation::create($validated + ['status' => 'PENDING']);

        return redirect()->route('donate')->with('donation_success', 'Thank you for your generosity. Your pledge has been recorded, and our team will follow up with payment instructions.');
    }
}
