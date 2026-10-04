<?php
namespace App\Models\Course;
use Illuminate\Database\Eloquent\Relations\Pivot;
class CourseTeacher extends Pivot { protected $table='course_teacher'; public $incrementing=true; protected $fillable=['course_id','teacher_id','sort_order']; protected $casts=['sort_order'=>'integer']; }
