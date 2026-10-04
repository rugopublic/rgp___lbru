<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\User\UserAccount;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

final class UserRepository implements UserRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::query()->withTrashed()->with(['account','groups'])->withCount('enrollments');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('account', fn (Builder $aq) => $aq->where('name', 'like', "%{$search}%"));
            });
        }
        if (!empty($filters['group'])) $query->whereHas('groups', fn (Builder $q) => $q->where('groups.public_id', $filters['group']));
        match ($filters['deleted'] ?? '') { 'only'=>$query->onlyTrashed(), 'all'=>null, default=>$query->withoutTrashed() };
        match ($filters['sort'] ?? '') {
            'date_asc' => $query->oldest(),
            'name_asc' => $query->orderBy(UserAccount::select('name')->whereColumn('user_accounts.user_id','users.id')),
            'name_desc' => $query->orderByDesc(UserAccount::select('name')->whereColumn('user_accounts.user_id','users.id')),
            default => $query->latest(),
        };
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Queries for admin and returns the matching persistence model.
     *
     * @param int|string $id
     * @return User
     */
    public function findForAdmin(int|string $id): User
    {
        return User::withTrashed()->with(['account','groups'])->withCount('enrollments')->where(function ($query) use ($id): void { $query->where('public_id', $id); if (is_numeric($id)) { $query->orWhere('id', (int) $id); } })->firstOrFail();
    }

    /**
     * Queries by id and returns the matching persistence model.
     *
     * @param int $id
     * @return User
     */
    public function findById(int $id): User { return User::query()->findOrFail($id); }

    /**
     * Queries by id for update and returns the matching persistence model.
     *
     * @param int $id
     * @return User
     */
    public function findByIdForUpdate(int $id): User { return User::query()->whereKey($id)->lockForUpdate()->firstOrFail(); }

    /**
     * Queries by email and returns the matching persistence model.
     *
     * @param string $email
     * @param bool $withTrashed
     * @return ?User
     */
    public function findByEmail(string $email, bool $withTrashed = false): ?User { $query = $withTrashed ? User::withTrashed() : User::query(); return $query->whereRaw('LOWER(email) = ?',[mb_strtolower($email)])->first(); }

    /**
     * Queries or persists public id exists for the user repository repository.
     *
     * @param string $publicId
     * @return bool
     */
    public function publicIdExists(string $publicId): bool { return User::withTrashed()->where('public_id', $publicId)->exists(); }

    /**
     * Queries or persists deletion grace impending for the user repository repository.
     *
     * @param ?int $daysLeft
     * @param bool $exact
     * @return \Illuminate\Support\Collection
     */
    public function deletionGraceImpending(?int $daysLeft = null, bool $exact = false): \Illuminate\Support\Collection { return User::deletionGraceImpending($daysLeft, $exact)->get(); }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return User
     */
    public function create(array $attributes): User { return User::create($attributes); }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param User $user
     * @param array $attributes
     * @return bool
     */
    public function update(User $user,array $attributes):bool{return $user->fill($attributes)->save();}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user):bool{return (bool)$user->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param User $user
     * @return bool
     */
    public function restore(User $user):bool{return (bool)$user->restore();}

    /**
     * Queries or persists force delete for the user repository repository.
     *
     * @param User $user
     * @return bool
     */
    public function forceDelete(User $user):bool{return (bool)$user->forceDelete();}

    /**
     * Synchronizes groups through the repository relationship.
     *
     * @param User $user
     * @param array $groupIds
     * @return void
     */
    public function syncGroups(User $user,array $groupIds):void{$user->groups()->sync(array_values(array_unique(array_map('intval',$groupIds))));}

    /**
     * Queries or persists account exists for the user repository repository.
     *
     * @param User $user
     * @return bool
     */
    public function accountExists(User $user): bool { return UserAccount::query()->whereKey($user->getKey())->exists(); }

    /**
     * Queries or persists create account for the user repository repository.
     *
     * @param User $user
     * @param array $attributes
     * @return UserAccount
     */
    public function createAccount(User $user, array $attributes): UserAccount { return UserAccount::create(['user_id' => $user->getKey(), ...$attributes]); }

    /**
     * Queries or persists update account for the user repository repository.
     *
     * @param User $user
     * @param array $attributes
     * @return bool
     */
    public function updateAccount(User $user,array $attributes):bool{ $account=$user->account()->firstOrCreate(['user_id'=>$user->id],['name'=>null]); return $account->fill($attributes)->save(); }
}
