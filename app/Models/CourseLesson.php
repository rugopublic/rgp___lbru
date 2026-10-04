<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\ContentMediaObserver;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Quiz as QuizModel;
#[ObservedBy([ContentMediaObserver::class])]
class CourseLesson extends Model { use SoftDeletes; protected $fillable=['module_id','title','description','video_url','duration','duration_minutes','is_preview','sort_order']; protected $casts=['duration'=>'integer','duration_minutes'=>'integer','is_preview'=>'boolean','sort_order'=>'integer'];

    /**
     * Returns the module relationship.
     * @return BelongsTo
     */
    public function module():BelongsTo{return $this->belongsTo(CourseModule::class,'module_id');}

    /**
     * Returns the steps relationship.
     * @return HasMany
     */
    public function steps():HasMany{return $this->hasMany(CourseStep::class,'lesson_id')->orderBy('sort_order');}

    /**
     * Returns the quizzes relationship.
     * @return MorphToMany
     */
    public function quizzes():MorphToMany{return $this->morphToMany(QuizModel::class,'quizzable');}

    /**
     * Handles media folder for the course lesson.
     * @return string
     */
    public function mediaFolder():string{return 'courses/'.($this->module?->course?->public_id ?? 'unknown').'/lessons/'.$this->id;}

    /**
     * Returns the video file url accessor value.
     * @return ?string
     */
    public function getVideoFileUrlAttribute(): ?string { if (!$this->video_url) return null; $path = str_contains($this->video_url, '/') ? $this->video_url : $this->mediaFolder().'/'.$this->video_url; return \Storage::disk('public')->url($path); } }
