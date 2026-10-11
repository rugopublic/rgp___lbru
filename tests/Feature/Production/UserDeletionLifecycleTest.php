<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\User\Delete\GraceExpired;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('sends the final account-deletion notice from the in-memory user and cleans private storage', function (): void {
    Storage::fake('private');
    Notification::fake();

    $user = User::factory()->create();
    $folder = $user->folder();

    Storage::disk('private')->put($folder.'/private.txt', 'private');

    $user->forceDelete();

    Notification::assertSentTo($user, GraceExpired::class);
    Storage::disk('private')->assertMissing($folder.'/private.txt');
    expect(is_subclass_of(GraceExpired::class, ShouldQueue::class))->toBeFalse();
});
