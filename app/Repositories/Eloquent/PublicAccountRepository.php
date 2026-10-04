<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ContentBookmark;
use App\Models\ContentComment;
use App\Models\CourseEnrollment;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\PublicAccountRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PublicAccountRepository implements PublicAccountRepositoryInterface
{

    /**
     * Queries or persists user for the public account repository repository.
     *
     * @param int $userId
     * @return User
     */
    public function user(int $userId): User
    {
        return User::query()->with('account')->findOrFail($userId);
    }

    /**
     * Queries or persists enrollments for the public account repository repository.
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function enrollments(int $userId, int $limit = 8): Collection
    {
        return CourseEnrollment::query()
            ->with('course')
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Queries or persists purchases for the public account repository repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function purchases(int $userId, int $perPage = 18): LengthAwarePaginator
    {
        return Transaction::query()
            ->with(['paymentIntent.purchasable'])
            ->where('buyer_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Queries or persists saved for the public account repository repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function saved(int $userId, int $perPage = 18): LengthAwarePaginator
    {
        return ContentBookmark::query()
            ->with('bookmarkable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Queries or persists comments for the public account repository repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function comments(int $userId, int $perPage = 18): LengthAwarePaginator
    {
        return ContentComment::query()
            ->with('commentable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
