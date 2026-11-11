<?php

namespace App\Notifications;

use App\Models\Teacher;
use App\Services\Auth\PasswordLinks;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when the Super Admin creates a teacher (or resends the invite).
 */
class TeacherInvitation extends Notification
{
    public function __construct(public readonly Teacher $teacher, public readonly string $link) {}

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
            ->subject(__('Your NexoraLMS site is ready'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your online class site has been created on NexoraLMS.'))
            ->line(__('**Your site:** :url', ['url' => $this->teacher->url()]))
            ->line(__('**Teacher panel:** :url', ['url' => $this->teacher->url('manage')]))
            ->line(__('**Login:** :email (or your mobile number)', ['email' => $notifiable->email]))
            ->action(__('Set your password'), $this->link)
            ->line(__('This link works once and expires in :hours hours. If it expires, ask NexoraLMS to send a new one.', ['hours' => PasswordLinks::INVITE_HOURS]))
            ->salutation(__('— The NexoraLMS team'));
    }
}
