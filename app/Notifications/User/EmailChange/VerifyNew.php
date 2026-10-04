<?php

namespace App\Notifications\User\EmailChange;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Represents an email notification sent to a new email the user wants to attach
 * to the account. Its purpose is proof of ownership.
 */
class VerifyNew extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The language string key for this notification.
     *
     * @var string
     */
    private const TRANS_KEY = 'mail.user.email_change_verify_new';

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
     * @throws Exception
     */
    public function toMail(object $notifiable): MailMessage
    {
        if (!$notifiable->emailUpdate || !$notifiable->emailUpdate->email) {
            Log::error('EmailUpdate missing for user', ['user_id' => $notifiable->id]);
            throw new Exception('Email update data is missing');
        }

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
            ->action(
                __(self::TRANS_KEY . ".action"),
                route(
                    'user.email.change.verify',
                    ['token' => $notifiable->emailUpdate->token]
                )
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
