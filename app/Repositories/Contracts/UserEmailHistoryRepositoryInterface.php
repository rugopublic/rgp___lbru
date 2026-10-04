<?php

declare(strict_types=1);
namespace App\Repositories\Contracts;
use App\Models\User\UserEmailHistory;
interface UserEmailHistoryRepositoryInterface
{

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserEmailHistory
     */
    public function create(array $attributes): UserEmailHistory;
}
