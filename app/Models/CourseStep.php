<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\CourseStepMediaObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mews\Purifier\Casts\CleanHtmlInput;
#[ObservedBy([CourseStepMediaObserver::class])]
class CourseStep extends Model { protected $fillable=['lesson_id','type','title','content','video_url','duration_seconds','quiz_data','sort_order']; protected $casts=['content'=>CleanHtmlInput::class,'quiz_data'=>'array','sort_order'=>'integer'];

    /**
     * Returns the lesson relationship.
     * @return BelongsTo
     */
    public function lesson():BelongsTo{return $this->belongsTo(CourseLesson::class,'lesson_id');} }
