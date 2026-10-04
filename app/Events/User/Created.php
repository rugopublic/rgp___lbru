<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired immediately after a new user is created.
 *
 * This event is intended for actions that should occur right after a user is
 * registered or created in the system. Typical use cases include:
 * - creating a sharded folder for storing user files,
 * - sending a welcome email,
 * - notifying external systems or services.
 *
 * @package App\Events\User
 *
 * @see \App\Listeners\FinalizeCreation Listener that creates the user's sharded folder.
 * @see User::folder() Method to retrieve the full sharded folder path for the user.
 *
 * @example
 * use App\Events\User\Created;
 * use App\Models\User;
 *
 * $user = User::create([
 *     'name' => 'Alice',
 *     'email' => 'alice@example.com',
 * ]);
 *
 * event(new Created($user));
 * $path = $user->folder('avatar.png');
 */
class Created
{
    use Dispatchable, SerializesModels;

    /**
     * The user that was just created.
     *
     * @var User The newly created user instance.
     */
    public User $user;

    /**
     * Create a new Created event instance.
     *
     * @param User $user The newly created user instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
