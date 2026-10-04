<?php
namespace App\Models\Course;
use App\Models\Course;
use App\Observers\TeacherObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
#[ObservedBy([TeacherObserver::class])]
class Teacher extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable=['avatar','title','motto','about'];

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName():string{return 'public_id';}

    /**
     * Handles folder for the teacher.
     * @return Attribute
     */
    protected function folder():Attribute{return Attribute::make(get:fn() => "teachers/{$this->public_id}");}

    /**
     * Returns the avatar url accessor value.
     * @return ?string
     */
    public function getAvatarUrlAttribute(): ?string {
        if (!$this->avatar) return null;
        $path = str_contains($this->avatar, '/') ? $this->avatar : $this->folder.'/'.$this->avatar;
        return \Storage::disk('public')->url($path);
    }

    /**
     * Handles media folder for the teacher.
     * @return string
     */
    public function mediaFolder(): string { return (string) $this->folder; }

    /**
     * Handles media disk for the teacher.
     *
     * @param string $field
     * @return string
     */
    public function mediaDisk(string $field): string { return 'public'; }

    /**
     * Handles media disks for the teacher.
     * @return array
     */
    public function mediaDisks(): array { return ['public']; }

    /**
     * Returns the courses relationship.
     * @return BelongsToMany
     */
    public function courses():BelongsToMany{return $this->belongsToMany(Course::class,'course_teacher')->using(CourseTeacher::class)->withPivot('sort_order')->orderByPivot('sort_order');}
}
