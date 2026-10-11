<?php

declare(strict_types=1);

it('keeps the production scheduler complete and duplicate-safe by construction', function () {
    $console = file_get_contents(base_path('routes/console.php'));
    $publishJob = file_get_contents(base_path('app/Jobs/PublishScheduledContentItem.php'));
    $scanner = file_get_contents(base_path('app/Console/Commands/ScanOrphanMedia.php'));

    expect($console)
        ->toContain("PublishScheduledContentCommand::class)->everyMinute()")
        ->toContain("ScanOrphanMediaCommand::class, ['--disk' => 'public']")
        ->toContain("config('files.product_disk', 'local')");

    expect($publishJob)
        ->toContain('ShouldQueue')
        ->toContain('WithoutOverlapping')
        ->toContain('publishIfDue');

    expect($scanner)
        ->toContain("{--delete : Delete folders older than the safety window}")
        ->toContain('ContentMediaOwnershipRepositoryInterface');
});

it('keeps stale development hot-file out of the production artifact', function () {
    expect(is_file(base_path('public/hot')))->toBeFalse();
});

it('keeps the documented administrator bootstrap command available', function () {
    $command = file_get_contents(base_path('app/Console/Commands/CreateAdminUser.php'));

    expect($command)
        ->toContain('class CreateAdminUser')
        ->toContain('likablog:admin');
});

    /**
     * Handles project files for contract for the operational contract test.
     *
     * @param string $root
     * @return array
     */
    function projectFilesForContract(string $root): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = file_get_contents($file->getPathname());
        }
    }

    return $files;
}
