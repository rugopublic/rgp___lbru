<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RecipeFile extends Model { protected $fillable=['recipe_id','name','description','file_path','is_paid'];

    /**
     * Handles casts for the recipe file.
     * @return array
     */
    protected function casts():array{return ['is_paid'=>'boolean'];}

    /**
     * Returns the recipe relationship.
     * @return BelongsTo
     */
    public function recipe():BelongsTo{return $this->belongsTo(Recipe::class);} }
