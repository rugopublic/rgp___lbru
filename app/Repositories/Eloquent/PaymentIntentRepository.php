<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PaymentIntent;
use App\Repositories\Contracts\PaymentIntentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

final class PaymentIntentRepository extends Repository implements PaymentIntentRepositoryInterface
{

    /**
     * Queries by idempotence key and returns the matching persistence model.
     *
     * @param string $key
     * @return ?Model
     */
    public function findByIdempotenceKey(string $key): ?Model { return PaymentIntent::query()->where('idempotence_key',$key)->first(); }

    /**
     * Queries by provider payment id and returns the matching persistence model.
     *
     * @param string $providerPaymentId
     * @return ?Model
     */
    public function findByProviderPaymentId(string $providerPaymentId): ?Model { return PaymentIntent::query()->where('provider_payment_id',$providerPaymentId)->first(); }

    /**
     * Queries reusable for user item and returns the matching persistence model.
     *
     * @param int $userId
     * @param string $purchasableType
     * @param int $purchasableId
     * @return ?Model
     */
    public function findReusableForUserItem(int $userId, string $purchasableType, int $purchasableId): ?Model {
        return PaymentIntent::query()->where('user_id',$userId)->where('purchasable_type',$purchasableType)->where('purchasable_id',$purchasableId)->whereIn('status',['pending','waiting_for_capture','processing'])->latest('id')->first();
    }

    /**
     * Queries by provider payment id for update and returns the matching persistence model.
     *
     * @param string $providerPaymentId
     * @return ?Model
     */
    public function findByProviderPaymentIdForUpdate(string $providerPaymentId): ?Model { return PaymentIntent::query()->where('provider_payment_id',$providerPaymentId)->with('purchasable')->lockForUpdate()->first(); }

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $intentId
     * @param int $userId
     * @return Model
     */
    public function findForUser(int $intentId, int $userId): Model { return PaymentIntent::query()->whereKey($intentId)->where('user_id',$userId)->with('purchasable')->firstOrFail(); }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model { return PaymentIntent::create($attributes); }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $intent
     * @param array $attributes
     * @return bool
     */
    public function update(Model $intent,array $attributes): bool { return $intent->fill($attributes)->save(); }
}
