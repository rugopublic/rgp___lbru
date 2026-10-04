<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A model for a user's profile data.
 *
 * @package App\Models\User
 *
 * @property int $user_id Primary key. An ID of a user owning this account.
 * @property ?string $name Real name of a user owning this account. Can be null.
 * @property Carbon|null $created_at A timestamp when this row got added to the table.
 * @property Carbon|null $updated_at A timestamp when this row got last update in the table.
 */
class UserAccount extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'avatar'];

    /**
     * Get the user that owns this account.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
