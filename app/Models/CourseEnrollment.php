<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CourseEnrollment extends Model { protected $fillable=['user_id','course_id','transaction_id','status','access_expires_at','amount_paid'];

    /**
     * Handles casts for the course enrollment.
     * @return array
     */
    protected function casts():array{return ['access_expires_at'=>'datetime','amount_paid'=>'decimal:2'];}

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user():BelongsTo{return $this->belongsTo(User::class);}

    /**
     * Returns the course relationship.
     * @return BelongsTo
     */
    public function course():BelongsTo{return $this->belongsTo(Course::class);}

    /**
     * Returns the transaction relationship.
     * @return BelongsTo
     */
    public function transaction():BelongsTo{return $this->belongsTo(Transaction::class);}

    /**
     * Determines whether is active is true for the current object.
     * @return bool
     */
    public function isActive():bool{return $this->status==='active'&&($this->access_expires_at===null||$this->access_expires_at->isFuture());} }
