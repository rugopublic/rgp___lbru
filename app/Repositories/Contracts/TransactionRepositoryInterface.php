<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
interface TransactionRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * Queries the requested record and returns the matching persistence model.
     *
     * @param string|int $id
     * @return Model
     */
    public function find(string|int $id): Model;

    /**
     * Queries by payment intent id and returns the matching persistence model.
     *
     * @param int $paymentIntentId
     * @return ?Model
     */
    public function findByPaymentIntentId(int $paymentIntentId): ?Model;

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
     * @param Model $transaction
     * @param array $attributes
     * @return bool
     */
    public function update(Model $transaction, array $attributes): bool;
}
