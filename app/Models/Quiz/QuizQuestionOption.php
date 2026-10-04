<?php
namespace App\Models\Quiz;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class QuizQuestionOption extends Model { protected $table='quiz_question_options'; protected $fillable=['quiz_question_id','option_text','is_correct','sort_order'];

    /**
     * Handles casts for the quiz question option.
     * @return array
     */
    protected function casts():array{return ['is_correct'=>'boolean','sort_order'=>'integer'];}

    /**
     * Returns the question relationship.
     * @return BelongsTo
     */
    public function question():BelongsTo{return $this->belongsTo(QuizQuestion::class,'quiz_question_id');} }
