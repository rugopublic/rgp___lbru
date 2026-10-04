<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Transaction extends Model { protected $fillable=['transaction_id','buyer_id','items','amount','status','payment_method','payment_details','paid_at','payment_intent_id'];

    /**
     * Handles casts for the transaction.
     * @return array
     */
    protected function casts():array{return ['items'=>'array','payment_details'=>'array','amount'=>'decimal:2','paid_at'=>'datetime'];}

    /**
     * Returns the buyer relationship.
     * @return BelongsTo
     */
    public function buyer():BelongsTo{return $this->belongsTo(User::class,'buyer_id');}

    /**
     * Returns the payment intent relationship.
     * @return BelongsTo
     */
    public function paymentIntent():BelongsTo{return $this->belongsTo(PaymentIntent::class,'payment_intent_id');} }
