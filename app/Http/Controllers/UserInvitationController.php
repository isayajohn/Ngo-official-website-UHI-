<?php

namespace App\Http\Controllers;

use App\Models\UserInvitation;
use App\Services\UserInvitationService;
use App\Http\Requests\AcceptInvitationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserInvitationController extends Controller
{
    public function show(int $invitation, string $token, UserInvitationService $service): View
    {
        try {
            $record = $service->findValidInvitation($invitation, $token);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return view('auth.invitation-invalid');
        }

        return view('auth.accept-invitation', ['invitation' => $record, 'token' => $token]);
    }

    public function accept(AcceptInvitationRequest $request, int $invitation, string $token, UserInvitationService $service): RedirectResponse
    {
        $data = $request->validated();

        $record = $service->findValidInvitation($invitation, $token);

        try {
            $service->accept($record, $token, $data['password']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('filament.admin.auth.login')->with('status', 'Your account has been activated successfully. Please sign in.');
    }
}
