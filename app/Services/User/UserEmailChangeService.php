<?php

namespace App\Services\User;

use App\Notifications\User\EmailChange\ConfirmOld as ConfirmOldNotification;
use App\Notifications\User\EmailChange\VerifyNew as VerifyNewNotification;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\User\UserEmailUpdate;
use App\Models\User\UserEmailHistory;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserEmailUpdateRepositoryInterface;
use App\Repositories\Contracts\UserEmailHistoryRepositoryInterface;
use Illuminate\Support\Carbon;
use Random\RandomException;
use RuntimeException;
use Throwable;

/**
 * Class UserEmailChangeService
 *
 * Handles the full lifecycle of a user's email change:
 *  - `initiate`: Checks whether it's okay for a user to change the email, and if yes, send a verification email.
 *  - `verify`: Processes the verification of the new email the user wants to attack to their profile.
 *  - `confirm`: Finalizes the verification after the confirmation of the user's current email.
 *
 * This service encapsulates all the logic related to the user email change requests,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class UserEmailChangeService
{

    /**
     * Initializes the user email change service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @param UserEmailUpdateRepositoryInterface $emailUpdates
     * @param UserEmailHistoryRepositoryInterface $emailHistory
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserEmailUpdateRepositoryInterface $emailUpdates,
        private readonly UserEmailHistoryRepositoryInterface $emailHistory,
    ) {}
    /**
     * Checks whether there is an active email update for a given user.
     *
     * @param User $user
     *
     * @return bool
     */
    private function inProgressFor(User $user): bool
    {
        return $this->emailUpdates->findForUser($user->id) !== null;
    }

    /**
     * Checks whether there is an active cooldown for email change request
     * due to the user recently changing their password.
     *
     * @param User $user A user we check the cooldown for.
     *
     * @return bool TRUE only if the user has no active cooldown of this kind.
     */
    private function checkPasswordCooldown(User $user): bool
    {
        $cooldown = (int) config('user.email_password_cooldown');

        if(!$user->password_changed_at) {
            return true;
        }

        return $user->password_changed_at->diffInSeconds() >= $cooldown;
    }

    /**
     * Checks whether there is an active cooldown for email change request
     * due to the user recently changing their email.
     *
     * @param User $user A user we check the cooldown for.
     *
     * @return bool TRUE only if the user has no active cooldown of this kind.
     */
    private function checkEmailCooldown(User $user): bool
    {
        $cooldown = (int) config('user.email_change_cooldown');

        if(!$user->email_verified_at) {
            return true;
        }

        return $user->email_verified_at->diffInSeconds() >= $cooldown;
    }

    /**
     * Checks whether there is an active cooldown for email change request
     * due to the user recently cancelling the email change request.
     *
     * @param User $user A user we check the cooldown for.
     *
     * @return bool TRUE only if the user has no active cooldown of this kind.
     */
    private function checkRequestCooldown(User $user): bool
    {
        $cooldown = (int) config('user.email_request_cooldown');

        if(!$user->email_change_last_requested_at) {
            return true;
        }

        return $user->email_change_last_requested_at->diffInSeconds() >= $cooldown;
    }

    /**
     * Retrieves the UserEmailUpdate instance by an email verification token.
     * If the token has expired or is invalid, an error will be thrown.
     *
     * @param string $token
     *
     * @return UserEmailUpdate
     *
     * @throws RuntimeException
     */
    private function emailUpdateByToken(string $token): UserEmailUpdate
    {
        $emailUpdate = $this->emailUpdates->findByToken($token);

        if(!$emailUpdate) {
            throw new RuntimeException(__('mail.user.errors.token_not_found'));
        }

        if($emailUpdate->isExpired()) {
            throw new RuntimeException(__('mail.user.errors.token_expired'));
        }

        return $emailUpdate;
    }

    /**
     * Checks whether there is already an ongoing request for the given user.
     *
     * @param User $user
     *
     * @throws RuntimeException If it is active.
     */
    protected function checkActiveRequest(User $user): void
    {
        if($this->inProgressFor($user)) {
            throw new RuntimeException(__('mail.user.errors.request_currently_active'));
        }
    }

    /**
     * Checks whether there are any active cooldowns for email change request.
     *
     * @param User $user A user to check cooldowns for.
     *
     * @throws RuntimeException If any cooldown is active.
     */
    protected function checkCooldowns(User $user): void
    {
        if (!$this->checkPasswordCooldown($user)) {
            throw new RuntimeException(__('mail.user.errors.password_cooldown_active'));
        }

        if (!$this->checkEmailCooldown($user)) {
            throw new RuntimeException(__('mail.user.errors.email_cooldown_active'));
        }

        if (!$this->checkRequestCooldown($user)) {
            throw new RuntimeException(__('mail.user.errors.request_cooldown_active'));
        }
    }

    /**
     * Resends the letter to either verify the new email or to confirm the current email.
     *
     * @param User $user
     *
     * @throws RandomException
     */
    public function resend(User $user): void
    {
        $emailUpdate = $this->emailUpdates->findForUser($user->id);

        if(!$emailUpdate) {
            throw new RuntimeException(__('mail.user.errors.token_not_found'));
        }

        // Renew the token no matter which letter we're sending and save the new deadline.
        $emailUpdate->makeToken(renew: true, leaveDirty: true);
        $this->emailUpdates->update($emailUpdate, ['token' => $emailUpdate->token, 'expires_at' => $emailUpdate->expires_at]);

        if($emailUpdate->isVerified()) {
            // We're resending to the old email because the new one has already been verified.
            $user->notify(new ConfirmOldNotification());
        } else {
            // We're resending to the new email because it's not yet verified.
            $user->notify(new VerifyNewNotification());
        }
    }

    /**
     * Cancels an ongoing email change request for the given user.
     *
     * Sets a cooldown timestamp and removes the pending email update.
     *
     * @param User $user
     *
     * @throws Throwable
     */
    public function cancel(User $user): void
    {
        $user->email_change_last_requested_at = Carbon::now();

        DB::transaction(function () use ($user) {
            $emailUpdate = $this->emailUpdates->findForUser($user->id);
            if ($emailUpdate) {
                $this->emailUpdates->delete($emailUpdate);
            }

            $this->users->update($user, ['email_change_last_requested_at' => $user->email_change_last_requested_at]);
        });
    }

    /**
     * Initiates the email changing by checking related cooldowns and
     * sending the verification letter to the new email if all good.
     *
     * @param User $user The user initiating the email changing procedure.
     * @param string $email The new email this user wants to change to.
     *
     * @throws RandomException
     */
    public function initiate(User $user, string $email): void
    {
        // Check for any active email change requests.
        $this->checkActiveRequest($user);

        // Check for any active cooldowns.
        $this->checkCooldowns($user);

        // Create a new email update request for this user.
        $emailUpdate = new UserEmailUpdate();
        $emailUpdate->email = $email;
        $emailUpdate->makeToken(force: true);

        $this->emailUpdates->create([
            'user_id' => $user->id,
            'email' => $emailUpdate->email,
            'token' => $emailUpdate->token,
            'expires_at' => $emailUpdate->expires_at,
            'verified_at' => null,
        ]);

        // Send email to verify the new email address.
        $user->notify(new VerifyNewNotification());
    }

    /**
     * Verifies that the email the user wants to change to is really owned by them.
     * For this, a token is checked for validity.
     *
     * @param string $token
     *
     * @throws RandomException
     */
    public function verify(string $token): void
    {
        $emailUpdate = $this->emailUpdateByToken($token);

        // Mark the new address as verified.
        $emailUpdate->verify(leaveDirty: true);

        // Regenerate the token (do not refresh the old one, replace it!) for the next step.
        $emailUpdate->makeToken(renew: false, force: true, leaveDirty: true);
        $this->emailUpdates->update($emailUpdate, [
            'token' => $emailUpdate->token,
            'expires_at' => $emailUpdate->expires_at,
            'verified_at' => $emailUpdate->verified_at,
        ]);

        // Use that new token to send another confirmation, now to the old email.
        $user = $emailUpdate->user;
        $email = $user->email;

        $user->notify(new ConfirmOldNotification());
    }

    /**
     * Verifies that the owner of the old email (presumably the same user as the one changing the email)
     * is giving consent for finalizing the transfer from old email to new email.
     *
     * @param string $token
     *
     * @throws Throwable
     */
    public function confirm(string $token): void
    {
        $emailUpdate = $this->emailUpdateByToken($token);

        // Get the user from the token's email update.
        $user = $emailUpdate->user;

        // Snapshot the moment of change.
        $verifiedAt = Carbon::now();

        DB::transaction(function () use ($user, $emailUpdate, $verifiedAt): void {
            $this->emailHistory->create([
                'user_id' => $user->id,
                'email' => $user->email,
                'active_from' => $user->email_verified_at,
                'active_to' => $verifiedAt,
            ]);
            $this->emailUpdates->delete($emailUpdate);
            $this->users->update($user, [
                'email' => $emailUpdate->email,
                'email_verified_at' => $verifiedAt,
            ]);
        });
    }
}
