<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\CourseEnrollment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Public\PaymentFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('fulfills a successful product payment when the product was soft-deleted during checkout', function (): void {
    $user = User::factory()->create();

    $product = Product::query()->create([
        'public_id' => Str::random(28),
        'title' => 'Soft-deleted paid product',
        'description' => 'Product hidden while provider checkout is open.',
        'price' => '19.00',
        'is_visible' => true,
        'file' => 'download.pdf',
    ]);

    $intent = PaymentIntent::query()->create([
        'user_id' => $user->id,
        'purchasable_type' => Product::class,
        'purchasable_id' => $product->id,
        'amount' => '19.00',
        'currency' => 'RUB',
        'status' => 'pending',
        'provider' => 'yookassa',
        'provider_payment_id' => 'yk-soft-deleted-product',
        'idempotence_key' => (string) Str::uuid(),
    ]);

    $product->delete();

    app(PaymentFulfillmentService::class)->syncPayment('yk-soft-deleted-product', [
        'id' => 'yk-soft-deleted-product',
        'status' => 'succeeded',
        'amount' => ['value' => '19.00', 'currency' => 'RUB'],
        'metadata' => ['payment_intent_id' => (string) $intent->id],
    ]);

    expect($intent->fresh()->status)->toBe('succeeded');
    expect(Transaction::query()->where('payment_intent_id', $intent->id)->exists())->toBeTrue();
    expect(ProductPurchase::query()
        ->where('user_id', $user->id)
        ->where('product_id', $product->id)
        ->where('status', 'active')
        ->exists())->toBeTrue();
});

it('records a successful course payment when the course was soft-deleted during checkout', function (): void {
    $user = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Soft-deleted paid course',
        'price' => '49.00',
        'publish_status' => 'published',
    ]);

    $intent = PaymentIntent::query()->create([
        'user_id' => $user->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
        'amount' => '49.00',
        'currency' => 'RUB',
        'status' => 'pending',
        'provider' => 'yookassa',
        'provider_payment_id' => 'yk-soft-deleted-course',
        'idempotence_key' => (string) Str::uuid(),
    ]);

    $course->delete();

    app(PaymentFulfillmentService::class)->syncPayment('yk-soft-deleted-course', [
        'id' => 'yk-soft-deleted-course',
        'status' => 'succeeded',
        'amount' => ['value' => '49.00', 'currency' => 'RUB'],
        'metadata' => ['payment_intent_id' => (string) $intent->id],
    ]);

    expect($intent->fresh()->status)->toBe('succeeded');
    expect(Transaction::query()->where('payment_intent_id', $intent->id)->exists())->toBeTrue();
    expect(CourseEnrollment::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->where('status', 'active')
        ->exists())->toBeTrue();
});
