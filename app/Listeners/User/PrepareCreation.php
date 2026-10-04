<?php

namespace App\Listeners\User;

use App\Events\User\Creating as UserCreating;
use Hidehalo\Nanoid\Client;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Prepare a User entity before it is persisted to the database.
 * Responsibilities:
 * - Generate a unique public_id
 */
class PrepareCreation
{
    /**
     * Create the listener with its repository dependency.
     *
     * Laravel resolves listener constructor dependencies through the service container.
     * The event dispatcher invokes {@see handle()} with the event payload only, so
     * repository dependencies must not be declared as `handle()` parameters.
     *
     * @param UserRepositoryInterface $users Repository used to guarantee unique public IDs.
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Handle the User creating event.
     *
     * @param UserCreating $event User model that is about to be persisted.
     * @return void
     */
    public function handle(UserCreating $event): void
    {
        $user = $event->user;
        $client = new Client();

        // Generate a unique public_id
        do {
            $publicId = $client->generateId();
        } while ($this->users->publicIdExists($publicId));

        $user->public_id = $publicId;
    }
}
