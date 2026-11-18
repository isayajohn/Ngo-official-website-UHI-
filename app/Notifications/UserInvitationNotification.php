<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UserInvitation $invitation,
        private readonly string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitations.accept.show', [
            'invitation' => $this->invitation,
            'token' => $this->token,
        ]);

        return (new MailMessage)
            ->subject('You Have Been Invited to UGHI')
            ->view('emails.user-invitation', [
                'invitation' => $this->invitation,
                'url' => $url,
            ]);
    }
}
