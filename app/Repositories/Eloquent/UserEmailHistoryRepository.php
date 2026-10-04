<?php

declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\User\UserEmailHistory;
use App\Repositories\Contracts\UserEmailHistoryRepositoryInterface;
final class UserEmailHistoryRepository implements UserEmailHistoryRepositoryInterface
{

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserEmailHistory
     */
    public function create(array $attributes): UserEmailHistory { return UserEmailHistory::create($attributes); }
}
