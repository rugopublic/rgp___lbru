<?php

namespace App\Listeners\User;

use App\Events\User\Created as UserCreated;
use App\Services\Cookie\CookieConsentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class FinalizeCreation
 *
 * Handles post-creation setup for a new user.
 *
 * Responsibilities:
 * - Create a private storage folder for the user (based on their public_id)
 * - Ensure idempotency (safe re-runs won't duplicate or break anything)
 * - Initialize a cookie policy relationship for further valid user cookie management
 * - Log any file system errors instead of interrupting user creation
 *
 * This listener runs synchronously after the user record is successfully created.
 */
class FinalizeCreation
{
    /**
     * Resolve cookie-consent dependencies through Laravel's service container.
     */
    public function __construct(
        private readonly CookieConsentService $cookieConsentService,
    ) {
    }

    /**
     * Handle the event.
     *
     * @param UserCreated $event
     * @return void
     */
    public function handle(UserCreated $event): void
    {
        $user = $event->user ?? null;

        // Safety check — event must have a valid user instance
        if (!$user || !$user->public_id) {
            Log::warning('FinalizeCreation called without valid user instance or public_id.');
            return;
        }

        // Determine user’s private folder path
        $folder = $user->folder();

        try {
            // Idempotent folder creation: does nothing if it already exists
            if (!Storage::disk('private')->exists($folder)) {
                Storage::disk('private')->makeDirectory($folder, 0755, true);
                Log::info("Created private folder for user {$user->id} at [{$folder}].");
            } else {
                Log::debug("Skipped folder creation — already exists for user {$user->id}.");
            }

        } catch (Throwable $e) {
            // We never break user creation — just log the failure
            Log::error("Failed to create folder for user {$user->id}: {$e->getMessage()}");
        }

        try {
            $this->cookieConsentService->initializeUserCookieSettings($user);
        } catch (Throwable $e) {
            // Cookie-preference initialization must not roll back or break account creation.
            Log::error('Failed to initialize cookie consent settings for new user.', [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }
}
