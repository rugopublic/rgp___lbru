<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\Notify\User\GraceImpending as GraceImpendingCommand;
use App\Console\Commands\CleanTempUploads as CleanTempUploadsCommand;
use App\Console\Commands\PublishScheduledContent as PublishScheduledContentCommand;
use App\Console\Commands\ScanOrphanMedia as ScanOrphanMediaCommand;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GraceImpendingCommand::class)->twiceDailyAt(7, 21);

Schedule::command(CleanTempUploadsCommand::class)->hourly();

Schedule::command(PublishScheduledContentCommand::class)->everyMinute();
Schedule::command(ScanOrphanMediaCommand::class, ['--disk' => 'public'])->dailyAt('03:00');
if ((string) config('files.product_disk', 'local') !== 'public') {
    Schedule::command(ScanOrphanMediaCommand::class, ['--disk' => (string) config('files.product_disk', 'local')])->dailyAt('03:10');
}
