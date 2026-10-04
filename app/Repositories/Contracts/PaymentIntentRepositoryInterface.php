<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PaymentIntentRepositoryInterface
{

    /**
     * Queries by idempotence key and returns the matching persistence model.
     *
     * @param string $key
     * @return ?Model
     */
    public function findByIdempotenceKey(string $key): ?Model;

    /**
     * Queries by provider payment id and returns the matching persistence model.
     *
     * @param string $providerPaymentId
     * @return ?Model
     */
    public function findByProviderPaymentId(string $providerPaymentId): ?Model;

    /**
     * Queries reusable for user item and returns the matching persistence model.
     *
     * @param int $userId
     * @param string $purchasableType
     * @param int $purchasableId
     * @return ?Model
     */
    public function findReusableForUserItem(int $userId, string $purchasableType, int $purchasableId): ?Model;

    /**
     * Queries by provider payment id for update and returns the matching persistence model.
     *
     * @param string $providerPaymentId
     * @return ?Model
     */
    public function findByProviderPaymentIdForUpdate(string $providerPaymentId): ?Model;

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $intentId
     * @param int $userId
     * @return Model
     */
    public function findForUser(int $intentId, int $userId): Model;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $intent
     * @param array $attributes
     * @return bool
     */
    public function update(Model $intent, array $attributes): bool;
}
