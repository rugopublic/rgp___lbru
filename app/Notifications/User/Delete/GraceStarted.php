<?php

namespace App\Notifications\User\Delete;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for users that they have just requested to be permanently deleted.
 * This process is referred to as 'user deletion grace started'.
 */
class GraceStarted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of days of grace period before permanent deletion.
     *
     * @var int
     */
    public int $graceDays;

    /**
     * The language string key for this notification.
     *
     * @var string
     */
    private const TRANS_KEY = 'mail.user.deletion_grace_started';

    /**
     * Create a new notification instance.
     *
     * @param ?int $graceDays Number of days in the grace period
     */
    public function __construct(?int $graceDays = null)
    {
        $this->graceDays = (int) ($graceDays ?? config('user.delete_grace_days'));
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
        $wordedDays = trans_xchoice(self::TRANS_KEY . ".days_left", $this->graceDays);

        return (new MailMessage)
            ->subject(
                __(self::TRANS_KEY . ".subject", [ 'days_left' => $wordedDays ])
            )
            ->line(
                __(self::TRANS_KEY . ".summary", [ 'days_left' => $wordedDays ])
            )
            ->line(
                __(self::TRANS_KEY . ".reason_to_act")
            )
            ->action(
                __(self::TRANS_KEY . ".action"),
                route('user.delete.pending')
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
