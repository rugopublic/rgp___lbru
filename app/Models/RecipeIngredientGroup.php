<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class RecipeIngredientGroup extends Model { protected $fillable=['recipe_id','title','sort_order']; protected $casts=['sort_order'=>'integer'];

    /**
     * Returns the recipe relationship.
     * @return BelongsTo
     */
    public function recipe():BelongsTo{return $this->belongsTo(Recipe::class);}

    /**
     * Returns the ingredients relationship.
     * @return HasMany
     */
    public function ingredients():HasMany{return $this->hasMany(RecipeIngredient::class,'group_id')->orderBy('sort_order');} }
