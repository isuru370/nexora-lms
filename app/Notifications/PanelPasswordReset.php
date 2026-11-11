<?php

namespace App\Notifications;

use App\Services\Auth\PasswordLinks;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Forgot password?" on the teacher panel.
 */
class PanelPasswordReset extends Notification
{
    public function __construct(public readonly string $link) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Reset your NexoraLMS password'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We received a request to reset the password for your teacher panel.'))
            ->action(__('Reset password'), $this->link)
            ->line(__('This link works once and expires in :minutes minutes.', ['minutes' => PasswordLinks::RESET_MINUTES]))
            ->line(__('If you did not ask for this, you can ignore this email.'))
            ->salutation(__('— The NexoraLMS team'));
    }
}
