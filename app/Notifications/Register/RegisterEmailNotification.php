<?php

namespace App\Notifications\Register;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * A token used for the password reset link.
     *
     * @var string
     */
    public string $token;

    /**
     * The language string key for this notification.
     *
     * @var string
     */
    private const TRANS_KEY = 'mail.register.email_verification';

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
            ->action(
                __(self::TRANS_KEY . ".action"),
                route(
                    'register.verify',
                    ['token' => $this->token]
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
