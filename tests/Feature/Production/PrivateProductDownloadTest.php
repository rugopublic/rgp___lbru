<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('streams a private product file only to a user with an active purchase', function (): void {
    config(['files.product_disk' => 'local']);
    Storage::fake('local');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $product = Product::query()->create([
        'title' => 'Private download test',
        'price' => '15.00',
        'file' => 'download.pdf',
        'is_visible' => true,
    ]);

    Storage::disk('local')->put($product->mediaFolder().'/download.pdf', 'private content');
    ProductPurchase::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'amount_paid' => '15.00',
        'purchased_at' => now(),
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('products.download', $product->public_id));

    $response->assertOk()->assertDownload('download.pdf');
});

it('does not stream a private product file without an active purchase', function (): void {
    config(['files.product_disk' => 'local']);
    Storage::fake('local');

    $user = User::factory()->create(['email_verified_at' => now()]);
    $product = Product::query()->create([
        'title' => 'Unpurchased download test',
        'price' => '15.00',
        'file' => 'download.pdf',
        'is_visible' => true,
    ]);
    Storage::disk('local')->put($product->mediaFolder().'/download.pdf', 'private content');

    $this->actingAs($user)
        ->get(route('products.download', $product->public_id))
        ->assertForbidden();
});
