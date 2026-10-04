<?php
declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\ProductPurchase;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
final class ProductPurchaseRepository extends Repository implements ProductPurchaseRepositoryInterface
{

    /**
     * Queries active for user and returns the matching persistence model.
     *
     * @param int $userId
     * @param int $productId
     * @return ?Model
     */
    public function findActiveForUser(int $userId, int $productId): ?Model
    {
        return ProductPurchase::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Queries or persists upsert for payment for the product purchase repository repository.
     *
     * @param int $userId
     * @param int $productId
     * @param int $transactionId
     * @param string $amount
     * @return Model
     */
    public function upsertForPayment(int $userId, int $productId, int $transactionId, string $amount): Model
    {
        return ProductPurchase::query()->updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['transaction_id' => $transactionId, 'amount_paid' => $amount, 'purchased_at' => now(), 'status' => 'active'],
        );
    }
}
