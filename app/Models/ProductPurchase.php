<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductPurchase extends Model { protected $fillable=['user_id','product_id','transaction_id','amount_paid','purchased_at','status'];

    /**
     * Handles casts for the product purchase.
     * @return array
     */
    protected function casts():array{return ['purchased_at'=>'datetime','amount_paid'=>'decimal:2'];}

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user():BelongsTo{return $this->belongsTo(User::class);}

    /**
     * Returns the product relationship.
     * @return BelongsTo
     */
    public function product():BelongsTo{return $this->belongsTo(Product::class);}

    /**
     * Returns the transaction relationship.
     * @return BelongsTo
     */
    public function transaction():BelongsTo{return $this->belongsTo(Transaction::class);} }
