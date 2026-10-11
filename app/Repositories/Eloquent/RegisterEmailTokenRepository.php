<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Register\RegisterEmailToken;
use App\Repositories\Contracts\RegisterEmailTokenRepositoryInterface;

final class RegisterEmailTokenRepository implements RegisterEmailTokenRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?RegisterEmailToken
     */
    public function findByToken(string $token): ?RegisterEmailToken
    {
        return RegisterEmailToken::query()->where('token', $token)->first();
    }

    /**
     * Locks a matching verification-token row for atomic single-use consumption.
     *
     * @param string $token
     * @return ?RegisterEmailToken
     */
    public function findByTokenForUpdate(string $token): ?RegisterEmailToken
    {
        return RegisterEmailToken::query()
            ->where('token', $token)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Queries or persists delete by email for the register email token repository repository.
     *
     * @param string $email
     * @return void
     */
    public function deleteByEmail(string $email): void
    {
        RegisterEmailToken::query()->where('email', $email)->delete();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return RegisterEmailToken
     */
    public function create(array $attributes): RegisterEmailToken
    {
        return RegisterEmailToken::create($attributes);
    }

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param RegisterEmailToken $token
     * @return bool
     */
    public function delete(RegisterEmailToken $token): bool
    {
        return (bool) $token->delete();
    }
}
