<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Models\Course;
use App\Models\Product;
use App\Repositories\Contracts\PaymentIntentRepositoryInterface;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentService
{

    /**
     * Initializes the payment service instance and wires its required dependencies.
     *
     * @param PaymentIntentRepositoryInterface $intents
     * @param YooKassaClient $client
     * @param UserRepositoryInterface $users
     * @param CourseEnrollmentRepositoryInterface $courseEnrollments
     * @param ProductPurchaseRepositoryInterface $productPurchases
     * @return void
     */
    public function __construct(
        private readonly PaymentIntentRepositoryInterface $intents,
        private readonly YooKassaClient $client,
        private readonly UserRepositoryInterface $users,
        private readonly CourseEnrollmentRepositoryInterface $courseEnrollments,
        private readonly ProductPurchaseRepositoryInterface $productPurchases,
    ) {}

    /**
     * Orchestrates the create for operation for the payment service domain service.
     *
     * @param Model $item
     * @param int $userId
     * @return Model
     */
    public function createFor(Model $item, int $userId): Model
    {
        $amount = number_format((float)$item->price,2,'.','');
        if ((float)$amount <= 0) throw new RuntimeException('Для бесплатного материала платёж не требуется.');
        if ($item instanceof Product && (!$item->is_visible || blank($item->file))) throw new RuntimeException('Товар временно недоступен для покупки.');
        if ($item instanceof Course && $item->publish_status?->value !== 'published') throw new RuntimeException('Курс ещё не опубликован.');

        if ($item instanceof Product && $this->productPurchases->findActiveForUser($userId, (int) $item->id)) {
            throw new RuntimeException('Этот товар уже куплен и доступен в вашем профиле.');
        }

        if ($item instanceof Course && $this->courseEnrollments->findActiveForUser($userId, (int) $item->id)) {
            throw new RuntimeException('Доступ к этому курсу уже активен в вашем профиле.');
        }

        $intent = DB::transaction(function () use ($userId, $item, $amount): Model {
            $this->users->findByIdForUpdate($userId);
            $reusable = $this->intents->findReusableForUserItem($userId, $item::class, (int) $item->id);
            if ($reusable) {
                return $reusable;
            }
            return $this->intents->create([
                'user_id'=>$userId,
                'purchasable_type'=>$item::class,
                'purchasable_id'=>$item->id,
                'amount'=>$amount,
                'currency'=>'RUB',
                'status'=>'pending',
                'provider'=>'yookassa',
                'idempotence_key'=>(string) Str::uuid(),
            ]);
        });

        if ($intent->provider_payment_id && $intent->confirmation_url) {
            return $intent;
        }

        $key = (string) $intent->idempotence_key;
        if ($key === '') {
            throw new RuntimeException('Платёжный intent не имеет ключа идемпотентности.');
        }

        $payload = [
            'amount'=>['value'=>$amount,'currency'=>'RUB'],
            'capture'=>true,
            'description'=>mb_substr(($item instanceof Course ? 'Курс: ' : 'Товар: ').($item->title ?? $item->name),0,128),
            'confirmation'=>['type'=>'redirect','return_url'=>route('payments.result',['intent'=>$intent->id])],
            'metadata'=>['payment_intent_id'=>(string)$intent->id,'item_type'=>class_basename($item),'item_id'=>(string)$item->id],
        ];
        if ((bool) config('services.yookassa.receipt_enabled',false)) {
            $vatCode = config('services.yookassa.vat_code');
            if ($vatCode === null || $vatCode === '') {
                throw new RuntimeException('Для фискализации необходимо настроить YOOKASSA_VAT_CODE.');
            }
            $email = $this->users->findById($userId)->email;
            if ($email) $payload['receipt']=['customer'=>['email'=>$email],'items'=>[['description'=>mb_substr($item->title ?? $item->name,0,128),'quantity'=>'1.00','amount'=>['value'=>$amount,'currency'=>'RUB'],'vat_code'=>(int)$vatCode,'payment_subject'=>(string)config('services.yookassa.payment_subject','service'),'payment_mode'=>(string)config('services.yookassa.payment_mode','full_payment')]]];
        }

        try {
            $response = $this->client->createPayment($payload,$key);
        } catch (\Throwable $e) {
            // Keep the intent reusable. A network/DB failure can happen after YooKassa
            // accepted the request; canceling here would create a new charge on retry.
            try {
                $this->intents->update($intent,[
                    'payload'=>[
                        'error'=>$e->getMessage(),
                        'error_code'=>$e->getCode(),
                    ],
                ]);
            } catch (\Throwable) {
                // Preserve the original provider exception.
            }
            throw $e;
        }

        $this->intents->update($intent,[
            'provider_payment_id'=>$response['id'] ?? null,
            'confirmation_url'=>data_get($response,'confirmation.confirmation_url'),
            'payload'=>$response,
            'status'=>$response['status'] ?? 'pending',
        ]);
        if (empty($response['id']) || empty(data_get($response,'confirmation.confirmation_url'))) throw new RuntimeException('ЮKassa не вернула ссылку на оплату.');
        return $this->intents->findByIdempotenceKey($key) ?? $intent;
    }
}
