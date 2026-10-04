<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserPasswordChangeService
 *
 * Handles the full lifecycle of a user's password change:
 *  - `change`: Changes the password for a given user.
 *
 * This service encapsulates all the logic related to the user password change requests,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class UserPasswordChangeService
{

    /**
     * Initializes the user password change service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(private readonly UserRepositoryInterface $users) {}
    /**
     * Performs the password change for a given user.
     *
     * @param User $user
     * @param string $current
     * @param string $new
     *
     * @return void
     *
     * @throws AuthenticationException
     */
    public function change(User $user, string $current, string $new): void
    {
        $isPasswordCorrect = Hash::check($current, $user->password);

        if(!$isPasswordCorrect) {
            throw new AuthenticationException('Invalid credentials provided.',);
        }

        $this->users->update($user, [
            'password' => bcrypt($new),
            'password_changed_at' => Carbon::now(),
        ]);
    }
}
