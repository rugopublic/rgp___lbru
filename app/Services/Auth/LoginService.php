<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class LoginService
 *
 * Handles the full flow of user authentication:
 *  - `login`: Attempt to authenticate the user with the given credentials.
 *  - `logout`: De-authenticate the given user currently if currently logged in.
 *
 * This service encapsulates all the logic related to the password reset,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class LoginService
{

    /**
     * Initializes the login service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }
    /**
     * Attempts to retrieve the user by provided email and password.
     *
     * @param string $email
     * @param string $password
     *
     * @return User
     *
     * @throws ModelNotFoundException
     * @throws AuthenticationException
     */
    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail($email, withTrashed: true);

        if(!$user || $user->trashed()) {
            throw new ModelNotFoundException('No user matches the provided email.',
                [
                    'email' => $email
                ]
            );
        }

        if (!$user->email_verified_at) {
            throw new AuthenticationException('Email address is not verified.');
        }

        $isPasswordCorrect = Hash::check($password, $user->password);

        if(!$isPasswordCorrect) {
            throw new AuthenticationException('Invalid credentials for the user.',
                [
                    'user' => $user,
                ]
            );
        }

        return $user;
    }

    /**
     * Perform additional logout actions outside Http layer responsibility.
     *
     * @param User $user
     *
     * @return void
     */
    public function logout(User $user): void
    {
        // So far, no additional actions are performed on logout.
        // Here, in the future, it's possible to add additional monitoring,
        // for example, the timestamp of when did the user log in or out.
    }
}
