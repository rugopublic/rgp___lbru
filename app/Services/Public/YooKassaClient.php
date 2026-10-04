<?php

declare(strict_types=1);

namespace App\Services\Public;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class YooKassaClient
{
    private const API = 'https://api.yookassa.ru/v3';

    /**
     * Orchestrates the request operation for the yoo kassa client domain service.
     * @return mixed
     */
    private function request()
    {
        $shopId = (string) config('services.yookassa.shop_id');
        $secret = (string) config('services.yookassa.secret_key');
        if ($shopId === '' || $secret === '') {
            throw new RuntimeException('ЮKassa не настроена: отсутствует идентификатор магазина или секретный ключ.');
        }
        return Http::withBasicAuth($shopId,$secret)->acceptJson()->asJson()->timeout(20);
    }

    /** @param array<string,mixed> $payload */
    public function createPayment(array $payload, ?string $idempotenceKey = null): array
    {
        $key = $idempotenceKey ?: (string) Str::uuid();
        $response = $this->request()->withHeaders(['Idempotence-Key' => $key])->post(self::API.'/payments',$payload);
        return $this->decode($response);
    }

    /**
     * Orchestrates the get payment operation for the yoo kassa client domain service.
     *
     * @param string $paymentId
     * @return array
     */
    public function getPayment(string $paymentId): array
    {
        return $this->decode($this->request()->get(self::API.'/payments/'.rawurlencode($paymentId)));
    }

    /** @return array<string,mixed> */
    private function decode(Response $response): array
    {
        $json = $response->json();
        if (!is_array($json)) $json = [];
        if ($response->failed()) {
            $message = (string) data_get($json,'description', 'ЮKassa вернула ошибку API.');
            throw new RuntimeException($message, $response->status());
        }
        return $json;
    }
}
