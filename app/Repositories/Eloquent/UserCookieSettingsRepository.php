<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User\UserCookieSettings;
use App\Repositories\Contracts\UserCookieSettingsRepositoryInterface;

final class UserCookieSettingsRepository implements UserCookieSettingsRepositoryInterface
{

    /**
     * Queries or persists exists for user for the user cookie settings repository repository.
     *
     * @param int $userId
     * @return bool
     */
    public function existsForUser(int $userId): bool
    {
        return UserCookieSettings::query()->whereKey($userId)->exists();
    }

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $userId
     * @return ?UserCookieSettings
     */
    public function findForUser(int $userId): ?UserCookieSettings
    {
        return UserCookieSettings::query()->whereKey($userId)->first();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserCookieSettings
     */
    public function create(array $attributes): UserCookieSettings
    {
        return UserCookieSettings::create($attributes);
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param UserCookieSettings $settings
     * @param array $attributes
     * @return bool
     */
    public function update(UserCookieSettings $settings, array $attributes): bool
    {
        return $settings->fill($attributes)->save();
    }
}
