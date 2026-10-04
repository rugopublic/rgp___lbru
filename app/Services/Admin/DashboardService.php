<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\OperationalRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Services\Public\PublicationService;
use Illuminate\Support\Collection;

final class DashboardService
{

    /**
     * Initializes the dashboard service instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $articles
     * @param RecipeRepositoryInterface $recipes
     * @param CourseRepositoryInterface $courses
     * @param OperationalRepositoryInterface $operations
     * @param PublicationService $publication
     * @return void
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
        private readonly RecipeRepositoryInterface $recipes,
        private readonly CourseRepositoryInterface $courses,
        private readonly OperationalRepositoryInterface $operations,
        private readonly PublicationService $publication,
    ) {
    }

    /** @return array{attention:array<string,int>,events:Collection,failedJobs:Collection,recentChanges:Collection} */
    public function overview(): array
    {
        $now = now();
        $events = $this->publication->calendar($now, $now->copy()->addDays(14));

        $recentChanges = collect()
            ->merge($this->articles->recentlyModified(6)->map(fn ($item) => [
                'type' => 'article',
                'label' => 'Статья',
                'title' => $item->title,
                'updated_at' => $item->updated_at,
                'url' => route('admin.articles.edit', $item),
            ]))
            ->merge($this->recipes->recentlyModified(6)->map(fn ($item) => [
                'type' => 'recipe',
                'label' => 'Рецепт',
                'title' => $item->title,
                'updated_at' => $item->updated_at,
                'url' => route('admin.recipes.edit', $item),
            ]))
            ->merge($this->courses->recentlyModified(6)->map(fn ($item) => [
                'type' => 'course',
                'label' => 'Курс',
                'title' => $item->title,
                'updated_at' => $item->updated_at,
                'url' => route('admin.courses.edit', $item),
            ]))
            ->sortByDesc('updated_at')
            ->take(8)
            ->values();

        return [
            'attention' => [
                'drafts' => $this->articles->draftCount() + $this->recipes->draftCount() + $this->courses->draftCount(),
                'failed_jobs' => $this->operations->failedJobsCount(),
                'scheduled' => $events->count(),
            ],
            'events' => $events,
            'failedJobs' => $this->operations->latestFailedJobs(5),
            'recentChanges' => $recentChanges,
        ];
    }
}
