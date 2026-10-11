<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\Course;
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
    public function findByProviderPaymentIdForUpdate(string $providerPaymentId): ?Model
    {
        $intent = PaymentIntent::query()
            ->where('provider_payment_id', $providerPaymentId)
            ->lockForUpdate()
            ->first();

        return $intent ? $this->loadPurchasableIncludingTrashed($intent) : null;
    }

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $intentId
     * @param int $userId
     * @return Model
     */
    public function findForUser(int $intentId, int $userId): Model
    {
        $intent = PaymentIntent::query()
            ->whereKey($intentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $this->loadPurchasableIncludingTrashed($intent);
    }

    /**
     * Resolves purchasable content that may have been soft-deleted after checkout began.
     *
     * A pending provider payment remains valid even if an administrator temporarily
     * hides the product or course while the customer completes the hosted checkout.
     * Fulfillment still needs the original row to create the transaction and grant the
     * purchase/enrollment record.
     */
    private function loadPurchasableIncludingTrashed(PaymentIntent $intent): PaymentIntent
    {
        $purchasable = match ($intent->purchasable_type) {
            Product::class => Product::withTrashed()->find($intent->purchasable_id),
            Course::class => Course::withTrashed()->find($intent->purchasable_id),
            default => $intent->purchasable,
        };

        $intent->setRelation('purchasable', $purchasable);

        return $intent;
    }

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
