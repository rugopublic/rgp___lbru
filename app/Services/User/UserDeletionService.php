<?php

namespace App\Services\User;

use App\Exceptions\User\DeletionAlreadyRequestedException;
use App\Exceptions\User\DeletionNotRequestedException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class UserDeletionService
 *
 * Handles the full lifecycle of a user's deletion:
 *  - `initiate`: Checks whether it's okay to soft delete a user, and if yes, soft deletes them.
 *  - `restore`: Checks whether it's okay to restore a soft deleted user, and if yes, restores them.
 *  - `finalize`: Proceeds with hard deleting the user from the database.
 *
 * This service encapsulates all the logic related to the user deletion,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class UserDeletionService
{

    /**
     * Initializes the user deletion service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(private readonly UserRepositoryInterface $users) {}
    /**
     * Checks whether there is an active deletion process for a given user.
     *
     * @param User $user A user to check.
     *
     * @return bool
     */
    private function inProgressFor(User $user): bool
    {
        return $user->trashed();
    }

    /**
     * Finalizes the deletion process for the given user.
     *
     * @param User $user The user whose deletion to finalize.
     *
     * @return bool Whether the finalization was successful.
     *
     * @throws DeletionNotRequestedException
     * @throws ModelNotFoundException
     */
    public function finalize(User $user): bool
    {
        if(!$user->exists) {
            throw new ModelNotFoundException(
                "Cannot delete user: record does not exist in database."
            );
        } elseif(!$user->trashed()) {
            throw new DeletionNotRequestedException(
                "User #{$user->id} is not scheduled for deletion."
            );
        }

        return $this->users->forceDelete($user);
    }

    /**
     * Cancels the deletion process for the given user.
     *
     * @param User $user The user whose deletion to cancel.
     *
     * @return bool Whether the cancellation was successful.
     *
     * @throws DeletionNotRequestedException
     * @throws ModelNotFoundException
     */
    public function restore(User $user): bool
    {
        if(!$user->exists) {
            throw new ModelNotFoundException(
                "Cannot delete user: record does not exist in database."
            );
        } elseif(!$user->trashed()) {
            throw new DeletionNotRequestedException(
                "User #{$user->id} is not scheduled for deletion."
            );
        }

        return $this->users->restore($user);
    }

    /**
     * Initiates the deletion process for the given user.
     *
     * @param User $user The user to soft-delete.
     *
     * @return bool Whether the user was successfully soft-deleted.
     *
     * @throws DeletionAlreadyRequestedException
     * @throws ModelNotFoundException
     */
    public function initiate(User $user): bool
    {
        if(!$user->exists) {
            throw new ModelNotFoundException(
                "Cannot delete user: record does not exist in database."
            );
        } elseif($user->trashed()) {
            throw new DeletionAlreadyRequestedException(
                "User #{$user->id} is already scheduled for deletion."
            );
        }

        return $this->users->delete($user);
    }
}
