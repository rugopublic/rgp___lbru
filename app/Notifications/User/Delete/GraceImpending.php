<?php

namespace App\Notifications\User\Delete;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for users that they are soon to be permanently deleted.
 * This process is referred to as 'user deletion grace impending'.
 */
class GraceImpending extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of days until the hard deletion.
     *
     * @var int
     */
    public int $daysLeft;

    /**
     * The language string key for this notification.
     *
     * @var string
     */
    public const TRANS_KEY = 'mail.user.deletion_grace_impending';

    /**
     * Create a new notification instance.
     *
     * @param ?int $daysLeft
     */
    public function __construct(?int $daysLeft = null)
    {
        $this->daysLeft = (int) ($daysLeft ?? config('user.delete_grace_notify_days'));
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
        $wordedDays = trans_xchoice(self::TRANS_KEY . ".days_left", $this->daysLeft);

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
                route('user.restore')
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
