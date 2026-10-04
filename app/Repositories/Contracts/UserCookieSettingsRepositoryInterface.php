<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User\UserCookieSettings;

interface UserCookieSettingsRepositoryInterface
{

    /**
     * Queries or persists exists for user for the user cookie settings repository interface repository.
     *
     * @param int $userId
     * @return bool
     */
    public function existsForUser(int $userId): bool;

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $userId
     * @return ?UserCookieSettings
     */
    public function findForUser(int $userId): ?UserCookieSettings;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserCookieSettings
     */
    public function create(array $attributes): UserCookieSettings;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param UserCookieSettings $settings
     * @param array $attributes
     * @return bool
     */
    public function update(UserCookieSettings $settings, array $attributes): bool;
}
