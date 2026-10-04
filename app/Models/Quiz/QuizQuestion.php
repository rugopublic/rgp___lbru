<?php
namespace App\Models\Quiz;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class QuizQuestion extends Model { protected $table='quiz_questions'; protected $fillable=['quiz_id','question_text','sort_order']; protected $casts=['sort_order'=>'integer'];

    /**
     * Returns the quiz relationship.
     * @return BelongsTo
     */
    public function quiz():BelongsTo{return $this->belongsTo(Quiz::class);}

    /**
     * Returns the options relationship.
     * @return HasMany
     */
    public function options():HasMany{return $this->hasMany(QuizQuestionOption::class,'quiz_question_id')->orderBy('sort_order');} }
