<?php

namespace App\Services\Auth;

use App\Models\Auth\PasswordResetToken;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use App\Notifications\Auth\PasswordResetLink;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Random\RandomException;
use RuntimeException;
use Throwable;

/**
 * Class AuthPasswordResetService
 *
 * Handles the full lifecycle of a password reset for a user:
 *  - `send`: Submits an email with password reset link.
 *  - `validate`: Checks whether the password reset is possible with the given token.
 *  - `reset`: Replaces the user's current password with the provided one.
 *
 * This service encapsulates all the logic related to the password reset,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class AuthPasswordResetService
{

    /**
     * Initializes the auth password reset service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @param PasswordResetTokenRepositoryInterface $tokens
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordResetTokenRepositoryInterface $tokens,
    ) {}
    /**
     * Submits an email with the password reset link if there is a user
     * associated with this email.
     *
     * @param string $email The email to which the password reset link will be sent.
     *
     * @throws ModelNotFoundException
     *
     * @return bool Whether the submission succeeded.
     */
    public function send(string $email): bool
    {
        $user = $this->users->findByEmail($email);

        if(!$user) {
            throw new ModelNotFoundException('Nowhere to send the mail to.');
        }

        $token = new PasswordResetToken([
            'email' => $email,
            'created_at' => Carbon::now()
        ]);

        try {
            $token->generate();
        } catch (RandomException) {
            Log::error('Failed to generate email verification token', ['email' => $email]);
            return false;
        }

        try {
            DB::transaction(function () use ($email, $token) {
                // Delete the old token if exists before creating the new one.
                $this->tokens->deleteByEmail($email);
                $this->tokens->create($token->getAttributes());
            });
        } catch(Throwable $e) {
            Log::error('Failed to initialize the token', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        $user->notify(new PasswordResetLink($token->token));

        return true;
    }

    /**
     * Validates the password reset token from the link sent to the email.
     *
     * @param string $token The token to validate.
     *
     * @return bool Whether the validation succeeded.
     */
    public function validate(string $token): bool
    {
        $passwordResetToken = $this->tokens->findByToken($token);

        if(!$passwordResetToken) {
            // throw new ModelNotFoundException('This token does not exist.');
            return false;
        }

        if($passwordResetToken->isExpired()) {
            // throw new RuntimeException('This token is no longer valid.');
            return false;
        }

        if(!$passwordResetToken->user) {
            // throw new ModelNotFoundException('This token is no longer associated with a user.');
            return false;
        }

        // If none of the conditions above triggered, the token is valid, active, and associated with the user.
        return true;
    }

    /**
     * Updates the password of a user associated with a certain password reset token.
     *
     * @param string $token The token to consume (and to know for which user to set the password).
     * @param string $newPassword The password to overwrite the current one with.
     *
     * @return bool Whether the change succeeded.
     */
    public function reset(string $token, string $newPassword): bool
    {
        // Before proceeding, it's important to validate the token again.
        if(!$this->validate($token)) {
            // Error: this token has expired in the meantime.
            return false;
        }

        $passwordResetToken = $this->tokens->findByToken($token);
        $user = $passwordResetToken->user;

        $this->tokens->delete($passwordResetToken);

        return $this->users->update($user, ['password' => bcrypt($newPassword), 'password_changed_at' => now()]);
    }
}
