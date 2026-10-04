<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ContentBookmark;
use App\Models\ContentComment;
use App\Models\ContentLike;
use App\Repositories\Contracts\ContentInteractionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ContentInteractionRepository implements ContentInteractionRepositoryInterface
{

    /**
     * Queries or persists context for the content interaction repository repository.
     *
     * @param Model $model
     * @param ?int $userId
     * @return array
     */
    public function context(Model $model, ?int $userId = null): array
    {
        $type = $model::class;
        $id = (int) $model->getKey();

        return [
            'likeCount' => ContentLike::query()
                ->where('likeable_type', $type)
                ->where('likeable_id', $id)
                ->count(),
            'liked' => $userId !== null && ContentLike::query()
                ->where('user_id', $userId)
                ->where('likeable_type', $type)
                ->where('likeable_id', $id)
                ->exists(),
            'saved' => $userId !== null && ContentBookmark::query()
                ->where('user_id', $userId)
                ->where('bookmarkable_type', $type)
                ->where('bookmarkable_id', $id)
                ->exists(),
        ];
    }

    /**
     * Queries or persists published comments for the content interaction repository repository.
     *
     * @param Model $model
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function publishedComments(Model $model, int $perPage = 20): LengthAwarePaginator
    {
        return ContentComment::query()
            ->with('user.account')
            ->where('commentable_type', $model::class)
            ->where('commentable_id', $model->getKey())
            ->where('status', 'published')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Queries or persists toggle like for the content interaction repository repository.
     *
     * @param Model $model
     * @param int $userId
     * @return bool
     */
    public function toggleLike(Model $model, int $userId): bool
    {
        $like = ContentLike::query()
            ->where('user_id', $userId)
            ->where('likeable_type', $model::class)
            ->where('likeable_id', $model->getKey())
            ->first();

        if ($like) {
            $like->delete();
            return false;
        }

        ContentLike::query()->create([
            'user_id' => $userId,
            'likeable_type' => $model::class,
            'likeable_id' => $model->getKey(),
        ]);

        return true;
    }

    /**
     * Queries or persists toggle bookmark for the content interaction repository repository.
     *
     * @param Model $model
     * @param int $userId
     * @return bool
     */
    public function toggleBookmark(Model $model, int $userId): bool
    {
        $bookmark = ContentBookmark::query()
            ->where('user_id', $userId)
            ->where('bookmarkable_type', $model::class)
            ->where('bookmarkable_id', $model->getKey())
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return false;
        }

        ContentBookmark::query()->create([
            'user_id' => $userId,
            'bookmarkable_type' => $model::class,
            'bookmarkable_id' => $model->getKey(),
        ]);

        return true;
    }

    /**
     * Queries or persists add comment for the content interaction repository repository.
     *
     * @param Model $model
     * @param int $userId
     * @param string $body
     * @return Model
     */
    public function addComment(Model $model, int $userId, string $body): Model
    {
        return ContentComment::query()->create([
            'user_id' => $userId,
            'commentable_type' => $model::class,
            'commentable_id' => $model->getKey(),
            'body' => trim($body),
            'status' => 'published',
        ]);
    }

    /**
     * Queries or persists saved by user for the content interaction repository repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function savedByUser(int $userId, int $perPage = 18): LengthAwarePaginator
    {
        return ContentBookmark::query()
            ->with('bookmarkable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Queries or persists comments by user for the content interaction repository repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function commentsByUser(int $userId, int $perPage = 18): LengthAwarePaginator
    {
        return ContentComment::query()
            ->with('commentable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
