<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AccountRecoveryController extends Controller
{
    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        try {
            Password::sendResetLink(['email' => mb_strtolower(trim($request->validated('email')))]);
        } catch (Throwable $exception) {
            Log::warning('Unable to send a UGHI password reset email.', ['exception' => $exception::class]);
        }

        return redirect()->route('password.email.sent');
    }

    public function emailSent(): View
    {
        return view('auth.check-email');
    }

    public function reset(string $token, Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function updatePassword(ResetPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
                'password_changed_at' => now(),
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'status' => $user->status === 'LOCKED' ? 'ACTIVE' : $user->status,
                'is_active' => $user->status === 'LOCKED' ? true : $user->is_active,
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('filament.admin.auth.login')->with('status', 'Your password has been updated successfully. You can now sign in.');
        }

        return back()->withErrors([
            'email' => 'This password reset link is invalid or has expired. Please request a new one.',
        ]);
    }
}
