<?php

namespace App\Services\User;

use App\Exceptions\User\AccountAlreadyExistsException;
use App\Models\User;
use App\Models\User\UserAccount;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\User\AccountNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class UserManagementService
 *
 * Handles the full lifecycle of a user.
 *
 * This service encapsulates all the logic related to the user lifecycle requests,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class UserManagementService
{

    /**
     * Initializes the user management service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(private readonly UserRepositoryInterface $users) {}
    /**
     * Creates an account for the user that doesn't have the account.
     *
     * @param User $user A user which will own the created account.
     * @param array $data The data to be used for the account's initial setup.
     *
     * @return UserAccount The newly created account.
     *
     * @throws AccountAlreadyExistsException
     * @throws ModelNotFoundException
     */
    public function createAccount(User $user, array $data): UserAccount
    {
        if(!$user->exists) {
            throw new ModelNotFoundException("The provided user is missing in the database.");
        }

        if($this->users->accountExists($user)) {
            throw new AccountAlreadyExistsException("The provided user already has an account.");
        }

        return $this->users->createAccount($user, $data);
    }

    /**
     * Updates the user's name to the specified value.
     *
     * @param User $user A user which account will be updated.
     * @param ?string $value Nullable. The new value to assign instead of the old one.
     * @param bool $keepDirty Whether to not save the changes to the database immediately. Defaults to true.
     *
     * @return bool Whether the update succeeded.
     *
     * @throws AccountNotFoundException
     * @throws ModelNotFoundException
     */
    public function updateAccountName(User $user, ?string $value, bool $keepDirty = true): bool
    {
        if(!$user->exists) {
            throw new ModelNotFoundException("The provided user is missing in the database.");
        }

        if(!$this->users->accountExists($user)) {
            throw new AccountNotFoundException("The user #{$user->id}'s account is missing in the database.");
        }

        if ($keepDirty) {
            $user->account->name = $value;
            return true;
        }

        return $this->users->updateAccount($user, ['name' => $value]);
    }
}
