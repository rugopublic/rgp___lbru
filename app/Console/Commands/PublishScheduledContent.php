<?php
declare(strict_types=1);
namespace App\Console\Commands;
use App\Services\Public\PublicationService;
use Illuminate\Console\Command;
final class PublishScheduledContent extends Command
{
    protected $signature='content:publish-scheduled';
    protected $description='Публикует материалы, у которых наступила запланированная дата.';

    /**
     * Executes the Artisan command and its associated side effects.
     *
     * @param PublicationService $publisher
     * @return int
     */
    public function handle(PublicationService $publisher):int{ $counts=$publisher->dispatchDueJobs();$this->info(sprintf('Поставлено в очередь: статьи %d, рецепты %d, курсы %d.',$counts['articles'],$counts['recipes'],$counts['courses']));return self::SUCCESS; }
}
