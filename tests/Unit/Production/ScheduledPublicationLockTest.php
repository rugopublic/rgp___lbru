<?php

declare(strict_types=1);

use App\Jobs\PublishScheduledContentItem;
use Illuminate\Queue\Middleware\WithoutOverlapping;

it('bounds publication overlap locks and delays contention retries', function (): void {
    $job = new PublishScheduledContentItem('course', 123);

    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1);
    expect($middleware[0])->toBeInstanceOf(WithoutOverlapping::class);
    expect($middleware[0]->key)->toBe('publish:course:123');
    expect($middleware[0]->releaseAfter)->toBe(10);
    expect($middleware[0]->expiresAfter)->toBe(60);
    expect($job->timeout)->toBeLessThan($middleware[0]->expiresAfter);
});
