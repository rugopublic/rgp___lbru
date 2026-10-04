<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event that is dispatched **before** a User model is saved.
 *
 * This event is fired during the "saving" Eloquent lifecycle event,
 * before any changes are persisted to the database.
 *
 * It is triggered for both newly created and updated models.
 */
class Saving
{
    use Dispatchable, SerializesModels;

    /**
     * The User instance being saved.
     *
     * @var User
     */
    public User $user;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
