<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class UghiResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Your UGHI Password')
            ->view('emails.password-reset', [
                'firstName' => $notifiable->first_name ?: $notifiable->name,
                'url' => $url,
                'expiryMinutes' => config('auth.passwords.users.expire'),
            ]);
    }
}
