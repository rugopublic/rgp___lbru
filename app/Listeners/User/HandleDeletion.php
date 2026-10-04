<?php

namespace App\Listeners\User;

use App\Events\User\Deleted as UserDeleted;
use App\Notifications\User\Delete\GraceExpired as GraceExpiredNotification;
use App\Notifications\User\Delete\GraceStarted as GraceStartedNotification;
use Illuminate\Support\Facades\Storage;

/**
 * Listener for handling user deletions.
 *
 * Differentiates between soft deletes (grace period) and force deletes
 * (permanent removal), and sends appropriate email notifications.
 */
class HandleDeletion
{
    /**
     * Handle the user deletion event.
     *
     * @param UserDeleted $event
     * @return void
     */
    public function handle(UserDeleted $event): void
    {
        $user = $event->user;

        // Check whether it's a force delete
        if (method_exists($user, 'isForceDeleting') && $user->isForceDeleting()) {
            $user->notify(new GraceExpiredNotification());

            Storage::disk('local')->deleteDirectory($user->folder);

            // TODO: optionally clean pivot tables, tags, etc (can be done by CASCADE).
        } else {
            $user->notify(new GraceStartedNotification());

            // TODO: optionally log or schedule deferred final deletion
        }
    }
}
