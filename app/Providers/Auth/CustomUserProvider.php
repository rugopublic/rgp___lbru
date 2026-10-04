<?php

namespace App\Providers\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomUserProvider extends EloquentUserProvider
{

    /**
     * Handles retrieve by id for the custom user provider.
     *
     * @param mixed $identifier
     * @return ?Authenticatable
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->withTrashed()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    /**
     * Handles retrieve by token for the custom user provider.
     *
     * @param mixed $identifier
     * @param mixed $token
     * @return ?Authenticatable
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = $this->retrieveById($identifier);

        if (!$user) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        if (!$rememberToken || !hash_equals($rememberToken, $token)) {
            return null;
        }

        return $user;
    }

    /**
     * Handles retrieve by credentials for the custom user provider.
     *
     * @param array $credentials
     * @return ?Authenticatable
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        $query = $this->createModel()->newQuery()->withTrashed();

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Handles validate credentials for the custom user provider.
     *
     * @param Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? '';
        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
