<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentIntent extends Model
{
    protected $fillable = ['user_id','purchasable_type','purchasable_id','amount','currency','status','provider','provider_payment_id','confirmation_url','payload','idempotence_key','completed_at'];

    /**
     * Handles casts for the payment intent.
     * @return array
     */
    protected function casts(): array
    {
        return ['amount'=>'decimal:2','payload'=>'array','completed_at'=>'datetime'];
    }

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * Handles purchasable for the payment intent.
     * @return MorphTo
     */
    public function purchasable(): MorphTo { return $this->morphTo(); }

    /**
     * Returns the transaction relationship.
     * @return HasOne
     */
    public function transaction(): HasOne { return $this->hasOne(Transaction::class, 'payment_intent_id'); }
}
