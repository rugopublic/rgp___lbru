<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ProductPurchaseRepositoryInterface
{

    /**
     * Queries active for user and returns the matching persistence model.
     *
     * @param int $userId
     * @param int $productId
     * @return ?Model
     */
    public function findActiveForUser(int $userId, int $productId): ?Model;

    /**
     * Queries or persists upsert for payment for the product purchase repository interface repository.
     *
     * @param int $userId
     * @param int $productId
     * @param int $transactionId
     * @param string $amount
     * @return Model
     */
    public function upsertForPayment(int $userId, int $productId, int $transactionId, string $amount): Model;
}
