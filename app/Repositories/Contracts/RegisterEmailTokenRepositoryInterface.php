<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Register\RegisterEmailToken;

interface RegisterEmailTokenRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?RegisterEmailToken
     */
    public function findByToken(string $token): ?RegisterEmailToken;

    /**
     * Queries or persists delete by email for the register email token repository interface repository.
     *
     * @param string $email
     * @return void
     */
    public function deleteByEmail(string $email): void;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return RegisterEmailToken
     */
    public function create(array $attributes): RegisterEmailToken;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param RegisterEmailToken $token
     * @return bool
     */
    public function delete(RegisterEmailToken $token): bool;
}
