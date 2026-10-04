<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event that is fired after a user has been deleted.
 *
 * This can be either a soft delete or a force delete.
 *
 * @property User $user The user instance that was deleted
 */
class Deleted
{
    use Dispatchable, SerializesModels;

    /**
     * The deleted user instance.
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new event instance.
     *
     * @param User $user The user instance that was deleted
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
