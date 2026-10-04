<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Models\Course;
use App\Models\Product;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use App\Repositories\Contracts\PaymentIntentRepositoryInterface;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PaymentFulfillmentService
{

    /**
     * Initializes the payment fulfillment service instance and wires its required dependencies.
     *
     * @param PaymentIntentRepositoryInterface $intents
     * @param TransactionRepositoryInterface $transactions
     * @param ProductPurchaseRepositoryInterface $productPurchases
     * @param CourseEnrollmentRepositoryInterface $courseEnrollments
     * @return void
     */
    public function __construct(
        private readonly PaymentIntentRepositoryInterface $intents,
        private readonly TransactionRepositoryInterface $transactions,
        private readonly ProductPurchaseRepositoryInterface $productPurchases,
        private readonly CourseEnrollmentRepositoryInterface $courseEnrollments,
    ) {
    }

    /** @param array<string,mixed> $payment */
    public function syncPayment(string $providerPaymentId, array $payment): void
    {
        DB::transaction(function () use ($providerPaymentId, $payment): void {
            $intent = $this->intents->findByProviderPaymentIdForUpdate($providerPaymentId);
            if (!$intent) {
                return;
            }

            $this->assertProviderPaymentMatchesIntent($providerPaymentId, $intent, $payment);

            $status = (string) data_get($payment, 'status', $intent->status);
            $completedAt = $status === 'succeeded'
                ? ($intent->completed_at ?: now())
                : $intent->completed_at;

            $this->intents->update($intent, [
                'status' => $status,
                'payload' => $payment,
                'completed_at' => $completedAt,
            ]);

            if ($status !== 'succeeded' || $completedAt === null) {
                return;
            }

            $transaction = $this->transactions->findByPaymentIntentId((int) $intent->id);
            if (!$transaction) {
                $item = $intent->purchasable;
                if (!$item) {
                    return;
                }

                $transaction = $this->transactions->create([
                    'transaction_id' => 'YK-'.$providerPaymentId,
                    'payment_intent_id' => $intent->id,
                    'buyer_id' => $intent->user_id,
                    'items' => [[
                        'type' => $item instanceof Course
                            ? 'course'
                            : ($item instanceof Product ? 'product' : class_basename($item)),
                        'id' => $item->id,
                        'title' => $item->title ?? $item->name ?? '',
                    ]],
                    'amount' => $intent->amount,
                    'status' => 'completed',
                    'payment_method' => 'yookassa',
                    'payment_details' => $payment,
                    'paid_at' => now(),
                ]);
            }

            if ($intent->purchasable instanceof Course) {
                $course = $intent->purchasable;
                $this->courseEnrollments->upsertForPayment(
                    (int) $intent->user_id,
                    (int) $course->id,
                    (int) $transaction->id,
                    (string) $intent->amount,
                );
            } elseif ($intent->purchasable instanceof Product) {
                $this->productPurchases->upsertForPayment(
                    (int) $intent->user_id,
                    (int) $intent->purchasable->id,
                    (int) $transaction->id,
                    (string) $intent->amount,
                );
            }
        });
    }

    /** @param array<string,mixed> $payment */
    private function assertProviderPaymentMatchesIntent(
        string $providerPaymentId,
        Model $intent,
        array $payment,
    ): void {
        $storedProviderId = (string) ($intent->provider_payment_id ?? '');
        if ($storedProviderId !== '' && !hash_equals($storedProviderId, $providerPaymentId)) {
            throw new RuntimeException('Платёж ЮKassa не соответствует локальному payment intent.');
        }

        $providerAmount = data_get($payment, 'amount.value');
        $providerCurrency = strtoupper((string) data_get($payment, 'amount.currency', ''));
        $intentAmount = number_format((float) $intent->amount, 2, '.', '');
        $intentCurrency = strtoupper((string) ($intent->currency ?: 'RUB'));

        if ($providerAmount === null || number_format((float) $providerAmount, 2, '.', '') !== $intentAmount) {
            throw new RuntimeException('Сумма платежа ЮKassa не совпадает с суммой payment intent.');
        }

        if ($providerCurrency === '' || $providerCurrency !== $intentCurrency) {
            throw new RuntimeException('Валюта платежа ЮKassa не совпадает с payment intent.');
        }

        $metadataIntentId = data_get($payment, 'metadata.payment_intent_id');
        if ($metadataIntentId !== null && (string) $metadataIntentId !== (string) $intent->id) {
            throw new RuntimeException('Payment intent в метаданных ЮKassa не совпадает с локальным intent.');
        }
    }
}
