<?php
namespace App\Models\Quiz;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
class Quizzable extends MorphPivot { protected $table='quizzables'; public $incrementing=true; public $timestamps=true; protected $fillable=['quiz_id','quizzable_type','quizzable_id']; }
