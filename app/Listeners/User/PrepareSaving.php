<?php

namespace App\Listeners\User;

use App\Events\User\Saving as UserSaving;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

/**
 * Listener that handles the User "saving" event.
 *
 * This listener ensures that certain immutable fields, such as `public_id`,
 * cannot be modified during the save process. Any attempt to change them
 * will be reverted, and a warning will be logged.
 */
class PrepareSaving
{
    /**
     * Create a new listener instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserSaving $event
     * @return void
     */
    public function handle(UserSaving $event): void
    {
        $user = $event->user;

        // Check if public_id has been modified
        if ($user->exists && $user->isDirty('public_id')) {
            $original = $user->getOriginal('public_id');

            // Revert to the original value
            $user->public_id = $original;

            // Log a warning for auditing purposes
            Log::warning(
                "Attempted to change public_id for User #{$user->id}. "
                . "Reverting to original value '{$original}'."
            );
        }
    }
}
