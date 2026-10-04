<?php

namespace App\Listeners\User;

use App\Events\User\DeleteGraceImpending as UserDeleteGraceExpiresSoon;
use App\Notifications\User\Delete\GraceImpending as GraceImpendingNotification;
use Illuminate\Support\Facades\Mail;

/**
 * Listener for the DeleteGraceImpending event.
 *
 * This listener handles actions that should occur shortly before
 * a soft-deleted user's grace period expires.
 *
 * Example: send a notification to the user that their account
 * will soon be permanently deleted.
 */
class HandleDeleteGraceImpending
{
    /**
     * Create the event listener instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserDeleteGraceExpiresSoon $event
     * @return void
     */
    public function handle(UserDeleteGraceExpiresSoon $event): void
    {
        $user = $event->user;

        // Send the GraceImpending notification to the user.
        $user->notify(new GraceImpendingNotification());
    }
}
