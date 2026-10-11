<?php

namespace App\Notifications\User\Delete;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for users that they have just been permanently deleted.
 * This process is referred to as 'user deletion grace expired'.
 */
class GraceExpired extends Notification
{
    use Queueable;

    /**
     * The language string key for this notification.
     *
     * @var string
     */
    private const TRANS_KEY = 'mail.user.deletion_grace_expired';

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(
                __(self::TRANS_KEY . ".subject")
            )
            ->line(
                __(self::TRANS_KEY . ".summary")
            )
            ->line(
                __(self::TRANS_KEY . ".reason_to_act")
            )
            ->line(
                __(self::TRANS_KEY . ".footer")
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
