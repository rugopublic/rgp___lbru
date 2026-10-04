<?php

namespace App\Models;

use App\Models\User\UserMessage;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\User\UserAccount;
use App\Models\User\UserCookieSettings;
use App\Models\User\UserEmailHistory;
use App\Models\User\UserEmailUpdate;
use App\Models\User\UserBan;
use Carbon\CarbonPeriod;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use App\Events\User\Created as UserCreated;
use App\Events\User\Creating as UserCreating;
use App\Events\User\Saving as UserSaving;
use App\Events\User\Deleted as UserDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $public_id
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $email_change_last_requested_at
 * @property Carbon|null $password_changed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read string $folder Full sharded folder path for the user.
 *
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereId($value)
 * @method static Builder|User wherePublicId($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 *
 * @event UserCreating The model is being created, before insert.
 * @event UserCreated The model was created, after insert.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'email',
        'is_admin',
        'email_verified_at',
        'password_changed_at',
        'password',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'creating' => UserCreating::class,
        'created' => UserCreated::class,
        'saving' => UserSaving::class,
        'deleted' => UserDeleted::class,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_change_last_requested_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Scope a query to only include soft-deleted users with impending grace period.
     *
     * The user's grace period is considered impending if there are less than
     * a certain amount of days remaining before the hard deletion of that user.
     *
     * @param Builder $query The query to scope.
     * @param null|int $daysLeft The maximum amount of days remaining to consider the grace period impending.
     * @param bool $exact Whether to only include users having exactly `$daysLeft` days left to hard deletion.
     *
     * @return void
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    #[Scope]
    protected function deletionGraceImpending
    (
        Builder $query,
        ?int $daysLeft = null,
        bool $exact = false
    ): void
    {
        $graceDays = (int) config('user.delete_grace_days', 30);
        $daysLeft = (int) ($daysLeft ?? config('user.delete_grace_notify_days', 7));

        $now = Carbon::now();

        if($graceDays <= 0) {
            throw new LogicException(
                'The grace period must be a positive number of days.' .
                'Check the `user.delete_grace_days` configuration.'
            );
        }

        if ($daysLeft < 0) {
            throw new InvalidArgumentException('The value of $daysLeft cannot be negative.');
        } elseif ($daysLeft === 0) {
            Log::warning('The value of $daysLeft is zero. Returning empty result.');
            $query->whereRaw('1 = 0');
            return;
        } elseif ($daysLeft > $graceDays) {
            Log::warning("daysLeft ({$daysLeft}) exceeds graceDays ({$graceDays}). Returning empty result.");
            $query->whereRaw('1 = 0');
            return;
        }

        // Calculate the proper bounds for 'deleted_at'.
        // For the user to be included in query results, the following inequality should hold:
        //      $lowerBound ⩽ deleted_at ⩽ $upperBound

        $lowerBound = $now->copy()->subDays($graceDays);
        $upperBound = $now->copy()->subDays($graceDays - $daysLeft);

        if($exact)
            $lowerBound = $upperBound->copy()->subDay()->addSecond();

        $interval = CarbonPeriod::create($lowerBound, $upperBound);

        $query->onlyTrashed()->whereBetween('deleted_at', $interval);
    }

    /**
     * Get the full sharded folder path for the user.
     *
     * Structure example:
     *   users/N/6/j/8/s/3/x/W/N6j8s3xWkTqPzL1X9act3
     *
     * The first 8 characters of public_id are used for sharding,
     * while the full public_id identifies the user’s own folder.
     *
     * @param string $subPath Optional subpath (e.g. 'avatar.png')
     * @return string
     *
     * @throws RuntimeException If public_id is missing.
     */
    public function folder(string $subPath = ''): string
    {
        if (!$this->public_id) {
            throw new RuntimeException('User public_id is missing.');
        }

        // Create sharded part from first 8 characters
        $shard = Str::of($this->public_id)
            ->substr(0, 8)
            ->split(1)
            ->join('/');

        // Base folder path includes shard + full public_id
        $base = "users/{$shard}/{$this->public_id}";

        // Append optional subpath if provided
        return $subPath ? "{$base}/{$subPath}" : $base;
    }

    /**
     * Accessor for $user->folder.
     * Returns the base sharded folder path without subpath.
     *
     * @return string
     */
    public function getFolderAttribute(): string
    {
        return $this->folder();
    }

    /**
     * Checks whether the current user has admin privileges.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get the email update associated with the user.
     *
     * @return HasOne
     */
    public function emailUpdate(): HasOne
    {
        return $this->hasOne(UserEmailUpdate::class);
    }

    /**
     * Get the email change history for the user.
     *
     * @return HasMany
     */
    public function emailHistory(): HasMany
    {
        return $this->hasMany(UserEmailHistory::class);
    }

    /**
     * Get the account associated with the user.
     *
     * @return HasOne
     */
    public function account(): HasOne
    {
        return $this->hasOne(UserAccount::class);
    }

    /**
     * Get the cookies settings for this user.
     *
     * @return HasOne
     */
    public function cookieSettings(): HasOne
    {
        return $this->hasOne(UserCookieSettings::class);
    }

    /**
     * Handles bans for the user.
     * @return mixed
     */
    public function bans()
    {
        return $this->hasMany(UserBan::class);
    }

    /**
     * Handles active ban for the user.
     * @return mixed
     */
    public function activeBan()
    {
        return $this->hasOne(UserBan::class)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest();
    }

    /**
     * Returns the is banned accessor value.
     * @return bool
     */
    public function getIsBannedAttribute(): bool
    {
        return $this->activeBan()->exists();
    }

    /**
     * Handles messages for the user.
     * @return mixed
     */
    public function messages()
    {
        return $this->hasMany(UserMessage::class, 'user_id');
    }

    /**
     * Returns the groups relationship.
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')->withTimestamps();
    }

    /**
     * Returns the transactions relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class, 'buyer_id');
    }

    /**
     * Returns the content likes relationship.
     * @return HasMany
     */
    public function contentLikes(): HasMany { return $this->hasMany(ContentLike::class); }

    /**
     * Returns the content bookmarks relationship.
     * @return HasMany
     */
    public function contentBookmarks(): HasMany { return $this->hasMany(ContentBookmark::class); }

    /**
     * Returns the content comments relationship.
     * @return HasMany
     */
    public function contentComments(): HasMany { return $this->hasMany(ContentComment::class); }

    /**
     * Returns the payment intents relationship.
     * @return HasMany
     */
    public function paymentIntents(): HasMany { return $this->hasMany(PaymentIntent::class); }

    /**
     * Returns the enrollments relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Returns the step progress relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function legalAcceptances(): \Illuminate\Database\Eloquent\Relations\HasMany
    // {
    //     return $this->hasMany(LegalAcceptance::class);
    // }

    public function stepProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseStepProgress::class);
    }

    /**
     * Returns the quiz attempts relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quizAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
