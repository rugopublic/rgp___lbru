<?php

namespace App\Listeners\User;

use App\Events\User\Deleted as UserDeleted;
use App\Notifications\User\Delete\GraceExpired as GraceExpiredNotification;
use App\Notifications\User\Delete\GraceStarted as GraceStartedNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            // This event runs after the user row has been deleted. Send this one
            // notification synchronously from the in-memory snapshot; queued model
            // serialization would fail because the user no longer exists in the DB.
            try {
                $user->notify(new GraceExpiredNotification());
            } catch (Throwable $e) {
                Log::error('Failed to send account-deletion completion notice.', [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);
            }

            // User files are owned by the private disk, not the default/public disk.
            try {
                Storage::disk('private')->deleteDirectory($user->folder);
            } catch (Throwable $e) {
                Log::error('Failed to clean private storage for deleted user.', [
                    'user_id' => $user->id,
                    'folder' => $user->folder,
                    'exception' => $e,
                ]);
            }

            // TODO: optionally clean pivot tables, tags, etc (can be done by CASCADE).
        } else {
            $user->notify(new GraceStartedNotification());

            // TODO: optionally log or schedule deferred final deletion
        }
    }
}
