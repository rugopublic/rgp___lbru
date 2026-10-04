<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RecipeIngredient extends Model { protected $fillable=['recipe_id','group_id','reference_item_id','ingredient_id','name','quantity','unit','section','sort_order']; protected $casts=['quantity'=>'decimal:2','sort_order'=>'integer'];

    /**
     * Returns the recipe relationship.
     * @return BelongsTo
     */
    public function recipe():BelongsTo{return $this->belongsTo(Recipe::class);}

    /**
     * Returns the group relationship.
     * @return BelongsTo
     */
    public function group():BelongsTo{return $this->belongsTo(RecipeIngredientGroup::class,'group_id');}

    /**
     * Returns the reference item relationship.
     * @return BelongsTo
     */
    public function referenceItem():BelongsTo{return $this->belongsTo(ReferenceItem::class,'reference_item_id');}

    /**
     * Returns the ingredient relationship.
     * @return BelongsTo
     */
    public function ingredient():BelongsTo{return $this->referenceItem();} }
