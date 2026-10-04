<?php
declare(strict_types=1);
namespace App\Services\Public;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use Illuminate\Support\Collection;
use App\Jobs\PublishScheduledContentItem;
final class PublicationService
{

    /**
     * Initializes the publication service instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $articles
     * @param RecipeRepositoryInterface $recipes
     * @param CourseRepositoryInterface $courses
     * @return void
     */
    public function __construct(private readonly ArticleRepositoryInterface $articles, private readonly RecipeRepositoryInterface $recipes, private readonly CourseRepositoryInterface $courses) {}

    /**
     * Dispatches due jobs for asynchronous or scheduled processing.
     *
     * @param int $limitPerType
     * @return array
     */
    public function dispatchDueJobs(int $limitPerType = 50): array {
        $counts = ['articles'=>0,'recipes'=>0,'courses'=>0];
        foreach ($this->articles->dueForPublication($limitPerType) as $item) { PublishScheduledContentItem::dispatch('article',(int)$item->id); $counts['articles']++; }
        foreach ($this->recipes->dueForPublication($limitPerType) as $item) { PublishScheduledContentItem::dispatch('recipe',(int)$item->id); $counts['recipes']++; }
        foreach ($this->courses->dueForPublication($limitPerType) as $item) { PublishScheduledContentItem::dispatch('course',(int)$item->id); $counts['courses']++; }
        return $counts;
    }

    /**
     * Orchestrates the calendar operation for the publication service domain service.
     *
     * @param mixed $from
     * @param mixed $to
     * @return Collection
     */
    public function calendar($from,$to): Collection {
        $events=collect();
        foreach($this->articles->scheduledBetween($from,$to) as $item)$events->push(['type'=>'article','public_id'=>$item->public_id,'title'=>$item->title,'published_at'=>$item->publish_at,'status'=>'scheduled','url'=>route('articles.show',$item->public_id)]);
        foreach($this->recipes->scheduledBetween($from,$to) as $item)$events->push(['type'=>'recipe','public_id'=>$item->public_id,'title'=>$item->title,'published_at'=>$item->publish_at,'status'=>$item->visibility_status,'url'=>route('recipes.show',$item->public_id)]);
        foreach($this->courses->scheduledBetween($from,$to) as $item)$events->push(['type'=>'course','public_id'=>$item->public_id,'title'=>$item->title,'published_at'=>$item->publish_date,'status'=>$item->publish_status->value ?? (string)$item->publish_status,'url'=>route('courses.show',$item->public_id)]);
        return $events->sortBy('published_at')->values();
    }
}
