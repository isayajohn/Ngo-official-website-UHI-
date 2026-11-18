<?php

namespace App\Http\Controllers;

use App\Models\InvolvementApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvolvementApplicationController extends Controller
{
    public function store(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, InvolvementApplication::TYPES), 404);

        $rules = [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:180'],
            'interest_area' => ['nullable', 'string', 'max:180'],
            'availability' => ['nullable', 'string', 'max:180'],
            'message' => ['nullable', 'string', 'max:5000'],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];

        if ($type === 'partnership') {
            $rules['organization'] = ['required', 'string', 'max:180'];
            $rules['message'] = ['required', 'string', 'max:5000'];
        }
        if ($type === 'volunteer') {
            $rules['interest_area'] = ['required', 'string', 'max:180'];
        }

        $data = $request->validate($rules);
        unset($data['privacy_consent'], $data['website']);
        InvolvementApplication::create($data + ['type' => $type, 'status' => 'NEW', 'submitted_at' => now()]);

        return back()->with('involvement_success', 'Thank you. Your submission has been sent to the UGHI team. We will follow up using the contact details you provided.');
    }
}
