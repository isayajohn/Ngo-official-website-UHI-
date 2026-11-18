<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        ContactMessage::create($request->safe()->except('website') + ['status' => 'NEW']);

        return back()->with('contact_success', 'Thank you. Your message has been received by the UGHI team.');
    }
}
