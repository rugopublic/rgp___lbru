<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Auth\PasswordResetToken;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;

final class PasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?PasswordResetToken
     */
    public function findByToken(string $token): ?PasswordResetToken
    {
        return PasswordResetToken::query()->with('user')->where('token', $token)->first();
    }

    /**
     * Queries or persists delete by email for the password reset token repository repository.
     *
     * @param string $email
     * @return void
     */
    public function deleteByEmail(string $email): void
    {
        PasswordResetToken::query()->where('email', $email)->delete();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return PasswordResetToken
     */
    public function create(array $attributes): PasswordResetToken
    {
        return PasswordResetToken::create($attributes);
    }

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param PasswordResetToken $token
     * @return bool
     */
    public function delete(PasswordResetToken $token): bool
    {
        return (bool) $token->delete();
    }
}
