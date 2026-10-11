<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Recipe;
use App\Repositories\Eloquent\ArticleRepository;
use App\Repositories\Eloquent\RecipeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not republish an article manually hidden after its scheduled time', function (): void {
    $article = Article::query()->create([
        'title' => 'Manually hidden article',
        'description' => 'Should stay hidden.',
        'is_visible' => false,
        'publish_at' => now()->subMinute(),
    ]);

    expect($article->updated_at->greaterThan($article->publish_at))->toBeTrue();
    expect(app(ArticleRepository::class)->dueForPublication()->pluck('id'))->not->toContain($article->id);
    expect(app(ArticleRepository::class)->publishIfDue($article->id))->toBeFalse();
    expect($article->fresh()->is_visible)->toBeFalse();
});

it('still publishes an article scheduled before its publish time', function (): void {
    $publishAt = now()->subMinute();
    $article = Article::query()->create([
        'title' => 'Due scheduled article',
        'description' => 'Should become visible.',
        'is_visible' => false,
        'publish_at' => $publishAt,
    ]);

    DB::table('articles')->where('id', $article->id)->update([
        'created_at' => $publishAt->copy()->subMinutes(10),
        'updated_at' => $publishAt->copy()->subMinutes(10),
    ]);

    expect(app(ArticleRepository::class)->dueForPublication()->pluck('id'))->toContain($article->id);
    expect(app(ArticleRepository::class)->publishIfDue($article->id))->toBeTrue();
    expect($article->fresh()->is_visible)->toBeTrue();
});

it('does not republish a recipe demoted after its scheduled time', function (): void {
    $recipe = Recipe::query()->create([
        'title' => 'Demoted recipe',
        'description' => 'Should remain a draft.',
        'visibility_status' => 'draft',
        'publish_at' => now()->subMinute(),
    ]);

    expect($recipe->updated_at->greaterThan($recipe->publish_at))->toBeTrue();
    expect(app(RecipeRepository::class)->dueForPublication()->pluck('id'))->not->toContain($recipe->id);
    expect(app(RecipeRepository::class)->publishIfDue($recipe->id))->toBeFalse();
    expect($recipe->fresh()->visibility_status)->toBe('draft');
});

it('still publishes a recipe scheduled before its publish time', function (): void {
    $publishAt = now()->subMinute();
    $recipe = Recipe::query()->create([
        'title' => 'Due scheduled recipe',
        'description' => 'Should become published.',
        'visibility_status' => 'draft',
        'publish_at' => $publishAt,
    ]);

    DB::table('recipes')->where('id', $recipe->id)->update([
        'created_at' => $publishAt->copy()->subMinutes(10),
        'updated_at' => $publishAt->copy()->subMinutes(10),
    ]);

    expect(app(RecipeRepository::class)->dueForPublication()->pluck('id'))->toContain($recipe->id);
    expect(app(RecipeRepository::class)->publishIfDue($recipe->id))->toBeTrue();
    expect($recipe->fresh()->visibility_status)->toBe('published');
});
