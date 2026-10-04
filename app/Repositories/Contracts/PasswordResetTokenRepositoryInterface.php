<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Auth\PasswordResetToken;

interface PasswordResetTokenRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?PasswordResetToken
     */
    public function findByToken(string $token): ?PasswordResetToken;

    /**
     * Queries or persists delete by email for the password reset token repository interface repository.
     *
     * @param string $email
     * @return void
     */
    public function deleteByEmail(string $email): void;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return PasswordResetToken
     */
    public function create(array $attributes): PasswordResetToken;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param PasswordResetToken $token
     * @return bool
     */
    public function delete(PasswordResetToken $token): bool;
}
