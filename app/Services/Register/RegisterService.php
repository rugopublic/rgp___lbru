<?php

namespace App\Services\Register;

use App\Models\Register\RegisterEmailToken;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\RegisterEmailTokenRepositoryInterface;
use App\Repositories\Contracts\LegalAcceptanceRepositoryInterface;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Register\RegisterEmailNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Random\RandomException;
use Throwable;
use RuntimeException;

/**
 * Class RegisterService
 *
 * Handles the full lifecycle of registration of a guest:
 *  - `send`: Submits an email with tokenized verification link.
 *  - `validate`: Checks whether the password input is possible with the given token.
 *  - `finalize`: Sets the new user's current password with the provided one.
 *
 * This service encapsulates all the logic related to the registration,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class RegisterService
{

    /**
     * Initializes the register service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @param RegisterEmailTokenRepositoryInterface $tokens
     * @param LegalAcceptanceRepositoryInterface $legalAcceptances
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RegisterEmailTokenRepositoryInterface $tokens,
        private readonly LegalAcceptanceRepositoryInterface $legalAcceptances,
    ) {}
    /**
     * Submits an email with the email verification link if there is no user
     * already associated with this email.
     *
     * @param string $email The email to which the verification link will be sent.
     *
     * @return bool Whether the submission succeeded.
     */
    public function send(string $email, array $consent = []): bool
    {
        // If a user with the provided email already exists, we cannot proceed with the registration.
        $user = $this->users->findByEmail($email);

        if($user) {
            Log::error('Failed to reserve the token for this email.', ['email' => $email]);
            return false;
        }

        // Initialize the new token but not save it to the database yet.
        $token = new RegisterEmailToken([
            'email' => $email,
            'created_at' => Carbon::now(),
            'agreement_accepted_at' => $consent['accepted_at'] ?? null,
            'agreement_version' => $consent['agreement_version'] ?? null,
            'privacy_version' => $consent['privacy_version'] ?? null,
            'acceptance_ip' => $consent['ip'] ?? null,
            'acceptance_user_agent' => $consent['user_agent'] ?? null
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
            Log::error('Failed to initialize the registration token', [
                'email' => $email,
                'exception_class' => $e::class,
            ]);
            return false;
        }

        // Send the letter to the specified email if everything went okay.
        Notification::route('mail', $email)
            ->notify(new RegisterEmailNotification($token->token));

        return true;
    }

    /**
     * Validates the email verification token from the link sent to the email.
     *
     * @param string $token The token to validate.
     *
     * @return bool Whether the validation succeeded.
     */
    public function validate(string $token): bool
    {
        $emailToken = $this->tokens->findByToken($token);

        if(!$emailToken) {
            // throw new ModelNotFoundException('This token does not exist.');
            Log::warning('Registration verification token was not found.');
            return false;
        }

        if($emailToken->isExpired()) {
            Log::warning('Registration verification token has expired.');
            // throw new RuntimeException('This token is no longer valid.');
            return false;
        }

        // If none of the conditions above triggered, the token is valid, active, and ready for consumption.
        return true;
    }

    /**
     * Sets the password of a newly registered user.
     *
     * @param string $token The token to consume (and to know for which email to set the password).
     * @param string $password The password to set for the new user.
     *
     * @return bool Whether the process succeeded.
     */
    public function finalize(string $token, string $password): bool
    {
        try {
            return DB::transaction(function () use ($token, $password): bool {
                // Lock and re-check the token in the same transaction as account creation.
                // Concurrent requests cannot both consume the same verification token.
                $emailToken = $this->tokens->findByTokenForUpdate($token);

                if (!$emailToken || $emailToken->isExpired()) {
                    return false;
                }

                $user = $this->users->create([
                    'password' => bcrypt($password),
                    'email' => $emailToken->email,
                    'email_verified_at' => Carbon::now(),
                ]);

                $this->legalAcceptances->create([
                    'user_id' => $user->id,
                    'agreement_version' => $emailToken->agreement_version ?: config('legal.agreement_version'),
                    'privacy_version' => $emailToken->privacy_version ?: config('legal.privacy_version'),
                    'accepted_at' => $emailToken->agreement_accepted_at ?: now(),
                    'ip' => $emailToken->acceptance_ip,
                    'user_agent' => $emailToken->acceptance_user_agent,
                ]);

                if (!$this->tokens->delete($emailToken)) {
                    throw new RuntimeException('Registration token could not be consumed.');
                }

                return true;
            });
        } catch (Throwable $e) {
            // Never write the submitted bearer token or the exception message to logs.
            // Database exceptions may contain SQL/binding details.
            Log::error('Failed to finalize registration', [
                'exception_class' => $e::class,
            ]);

            return false;
        }
    }
}
