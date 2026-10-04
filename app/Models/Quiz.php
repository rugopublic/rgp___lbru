<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use App\Models\Quiz\QuizQuestion;
use App\Models\Quiz\Quizzable;
class Quiz extends Model { protected $fillable=['title','description','slug','is_published'];

    /**
     * Handles casts for the quiz.
     * @return array
     */
    protected function casts():array{return ['is_published'=>'boolean'];}

    /**
     * Handles booted for the quiz.
     * @return void
     */
    protected static function booted():void{static::creating(function(self $m){$m->slug??=unique_slug($m->title,'quizzes');});}

    /**
     * Returns the questions relationship.
     * @return HasMany
     */
    public function questions():HasMany{return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');}

    /**
     * Returns the quizzables relationship.
     * @return HasMany
     */
    public function quizzables():HasMany{return $this->hasMany(Quizzable::class);}

    /**
     * Returns the lessons relationship.
     * @return MorphToMany
     */
    public function lessons():MorphToMany{return $this->morphedByMany(CourseLesson::class,'quizzable')->using(Quizzable::class);}

    /**
     * Returns the articles relationship.
     * @return MorphToMany
     */
    public function articles():MorphToMany{return $this->morphedByMany(Article::class,'quizzable')->using(Quizzable::class);} }
