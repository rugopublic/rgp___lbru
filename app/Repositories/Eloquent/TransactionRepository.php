<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

final class TransactionRepository extends Repository implements TransactionRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Transaction::query()->with(['buyer.account'])->latest();
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('buyer', fn ($buyer) => $buyer->where('email', 'like', "%{$search}%"));
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Queries the requested record and returns the matching persistence model.
     *
     * @param string|int $id
     * @return Model
     */
    public function find(string|int $id): Model
    {
        return Transaction::query()->with(['buyer.account', 'paymentIntent'])->findOrFail($id);
    }

    /**
     * Queries by payment intent id and returns the matching persistence model.
     *
     * @param int $paymentIntentId
     * @return ?Model
     */
    public function findByPaymentIntentId(int $paymentIntentId): ?Model
    {
        return Transaction::query()->where('payment_intent_id',$paymentIntentId)->first();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return Transaction::create($attributes);
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $transaction
     * @param array $attributes
     * @return bool
     */
    public function update(Model $transaction, array $attributes): bool
    {
        return $transaction->fill($attributes)->save();
    }
}
