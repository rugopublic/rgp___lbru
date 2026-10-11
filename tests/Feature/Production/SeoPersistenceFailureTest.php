<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Course;
use App\Models\Polymorphic\Seo;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\ReferenceItem;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\RecipeStepRepositoryInterface;
use App\Repositories\Contracts\RecipeStructureRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ArticleService;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\ProductService;
use App\Services\Admin\Content\RecipeService;
use App\Services\Admin\Content\RecipeStructureService;
use App\Services\Admin\Content\ReferenceService;
use App\Services\Admin\Content\RichTextMediaService;
use App\Services\Admin\Course\CourseService;
use App\Services\Admin\Generic\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

function seoRepositoryThatRejectsUpdates(): SeoRepositoryInterface
{
    $seo = Mockery::mock(SeoRepositoryInterface::class);
    $seo->shouldReceive('firstOrCreate')->once()->andReturn(new Seo());
    $seo->shouldReceive('update')->once()->andReturn(false);

    return $seo;
}

function emptyRichTextMediaService(): RichTextMediaService
{
    return new RichTextMediaService(Mockery::mock(FileService::class));
}

function emptyContentMediaService(): ContentMediaService
{
    return new ContentMediaService(Mockery::mock(FileService::class));
}

it('aborts an article save when SEO persistence returns false', function (): void {
    $article = new Article();
    $repo = Mockery::mock(ArticleRepositoryInterface::class);
    $repo->shouldReceive('update')->once()->andReturn(true);

    $service = new ArticleService($repo, seoRepositoryThatRejectsUpdates(), emptyContentMediaService(), emptyRichTextMediaService());

    expect(fn () => $service->update($article, new User(), ['meta_title' => 'SEO title']))
        ->toThrow(RuntimeException::class, 'SEO статьи не сохранено.');
});

it('aborts a product save when SEO persistence returns false', function (): void {
    $product = new Product();
    $repo = Mockery::mock(ProductRepositoryInterface::class);
    $repo->shouldReceive('update')->once()->andReturn(true);

    $service = new ProductService($repo, seoRepositoryThatRejectsUpdates(), emptyContentMediaService());

    expect(fn () => $service->update($product, new User(), ['meta_title' => 'SEO title']))
        ->toThrow(RuntimeException::class, 'SEO товара не сохранено.');
});

it('aborts a reference save when SEO persistence returns false', function (): void {
    $item = new ReferenceItem();
    $repo = Mockery::mock(ReferenceRepositoryInterface::class);
    $repo->shouldReceive('update')->once()->andReturn(true);

    $service = new ReferenceService($repo, seoRepositoryThatRejectsUpdates(), emptyContentMediaService(), emptyRichTextMediaService());

    expect(fn () => $service->update($item, new User(), ['meta_title' => 'SEO title']))
        ->toThrow(RuntimeException::class, 'SEO справочника не сохранено.');
});

it('aborts a recipe save when SEO persistence returns false', function (): void {
    $recipe = new Recipe();
    $repo = Mockery::mock(RecipeRepositoryInterface::class);
    $repo->shouldReceive('update')->once()->andReturn(true);

    $service = new RecipeService(
        $repo,
        Mockery::mock(RecipeStepRepositoryInterface::class),
        seoRepositoryThatRejectsUpdates(),
        emptyContentMediaService(),
        emptyRichTextMediaService(),
        new RecipeStructureService(Mockery::mock(RecipeStructureRepositoryInterface::class)),
    );

    expect(fn () => $service->update($recipe, new User(), ['meta_title' => 'SEO title']))
        ->toThrow(RuntimeException::class, 'SEO рецепта не сохранено.');
});

it('aborts a course save when SEO persistence returns false', function (): void {
    $course = new Course();
    $repo = Mockery::mock(CourseRepositoryInterface::class);
    $repo->shouldReceive('update')->once()->andReturn(true);

    $service = new CourseService($repo, seoRepositoryThatRejectsUpdates(), emptyContentMediaService(), emptyRichTextMediaService());

    expect(fn () => $service->update($course, new User(), new Collection(['meta_title' => 'SEO title'])))
        ->toThrow(RuntimeException::class, 'SEO курса не сохранено.');
});
