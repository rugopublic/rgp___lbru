<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event that is dispatched shortly before a soft-deleted user's grace period expires.
 *
 * This event can be used to notify the user that their account will soon be permanently deleted.
 */
class DeleteGraceImpending
{
    use Dispatchable, SerializesModels;

    /**
     * The User instance whose grace period is about to expire.
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
