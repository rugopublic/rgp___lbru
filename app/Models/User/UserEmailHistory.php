<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

/**
 * Class UserEmailHistory.
 *
 * An instance represents a record that a certain email was verified and used
 * for a certain timeframe by a certain user. It is never the user's current email.
 *
 * @package App\Models\User
 *
 * @property int $id Primary key. An identifier of the row.
 * @property int $user_id Foreign key. Represents a user ID that had been using that email.
 * @property string $email An email address that was used by that user.
 * @property Carbon $active_from A timestamp indicating the moment when the user started using that email.
 * @property Carbon $active_to A timestamp indicating the moment when the user stopped using that email.
 * @property Carbon|null $created_at A timestamp when this row got added to the table.
 * @property Carbon|null $updated_at A timestamp when this row got last update in the table.
 */
class UserEmailHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_email_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['email', 'active_from', 'active_to'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    /**
     * Interact with the email usage timespan by the user.
     *
     * @return Attribute
     */
    protected function activeTimespan(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $_, array $attributes) => CarbonPeriod::create(
                $attributes['active_from'],
                $attributes['active_to']
            ),
            set: fn (CarbonPeriod $value) => [
                'active_from' => $value->getStartDate(),
                'active_to' => $value->getEndDate()
            ]
        );
    }

    /**
     * Interact with the duration of the email usage by the user.
     *
     * @return Attribute
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->active_from->diffInDays($this->active_to)
        );
    }

    /**
     * Get the user that was using the email.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
