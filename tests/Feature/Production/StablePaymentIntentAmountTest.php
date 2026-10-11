<?php

declare(strict_types=1);

use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('reuses a pending intent amount after the catalogue price changes', function (): void {
    config([
        'services.yookassa.shop_id' => 'shop-test',
        'services.yookassa.secret_key' => 'secret-test',
    ]);

    Http::fake([
        'https://api.yookassa.ru/v3/payments' => Http::response([
            'id' => 'yk-stable-amount',
            'status' => 'pending',
            'amount' => ['value' => '19.00', 'currency' => 'RUB'],
            'confirmation' => ['confirmation_url' => 'https://yookassa.example/confirm'],
        ], 201),
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $product = Product::query()->create([
        'public_id' => Str::random(28),
        'title' => 'Price changed after checkout started',
        'description' => 'Test product',
        'price' => '24.00',
        'is_visible' => true,
        'file' => 'manual.pdf',
    ]);

    $intent = PaymentIntent::query()->create([
        'user_id' => $user->id,
        'purchasable_type' => Product::class,
        'purchasable_id' => $product->id,
        'amount' => '19.00',
        'currency' => 'RUB',
        'status' => 'pending',
        'provider' => 'yookassa',
        'idempotence_key' => (string) Str::uuid(),
    ]);

    $this->actingAs($user)
        ->get(route('products.buy', $product->public_id))
        ->assertOk()
        ->assertSee('19 ₽')
        ->assertDontSee('24 ₽');

    $this->actingAs($user)
        ->post(route('payments.pay', [
            'type' => 'product',
            'id' => $product->public_id,
        ]), ['agree' => '1'])
        ->assertRedirect('https://yookassa.example/confirm');

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.yookassa.ru/v3/payments'
            && data_get($request->data(), 'amount.value') === '19.00';
    });

    expect((string) $intent->fresh()->amount)->toBe('19.00');
});
