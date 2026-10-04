<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event that is dispatched **before** a User is created.
 *
 * Fired during the "creating" Eloquent model event.
 */
class Creating
{
    use Dispatchable, SerializesModels;

    /**
     * The User instance being created.
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
