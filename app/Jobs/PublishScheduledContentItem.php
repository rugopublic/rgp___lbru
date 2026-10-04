<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;

final class PublishScheduledContentItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    /**
     * Initializes the publish scheduled content item instance and wires its required dependencies.
     *
     * @param string $type
     * @param int $id
     * @return void
     */
    public function __construct(
        public readonly string $type,
        public readonly int $id,
    ) {}

    /**
     * Handles middleware for the publish scheduled content item.
     * @return array
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping("publish:{$this->type}:{$this->id}")];
    }

    /**
     * Executes the queued/background operation and its associated side effects.
     *
     * @param ArticleRepositoryInterface $articles
     * @param RecipeRepositoryInterface $recipes
     * @param CourseRepositoryInterface $courses
     * @return void
     */
    public function handle(
        ArticleRepositoryInterface $articles,
        RecipeRepositoryInterface $recipes,
        CourseRepositoryInterface $courses,
    ): void {
        match ($this->type) {
            'article' => $articles->publishIfDue($this->id),
            'recipe' => $recipes->publishIfDue($this->id),
            'course' => $courses->publishIfDue($this->id),
            default => null,
        };
    }
}
