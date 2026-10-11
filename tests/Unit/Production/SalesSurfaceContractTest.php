<?php

declare(strict_types=1);

it('keeps the public sales funnel wired to real commerce actions', function () {
    $course = file_get_contents(base_path('resources/web/frontend/catalog/course.blade.php'));
    $product = file_get_contents(base_path('resources/web/frontend/catalog/product.blade.php'));
    $checkout = file_get_contents(base_path('resources/web/frontend/checkout/index.blade.php'));
    $related = file_get_contents(base_path('resources/web/frontend/components/related-products.blade.php'));

    expect($course)
        ->toContain("route('courses.buy'")
        ->toContain('number_format((float) $course->price')
        ->toContain('objective')
        ->toContain('skills')
        ->toContain('course->lessons')
        ->toContain('course->teachers');

    expect($product)
        ->toContain("route('products.buy'")
        ->toContain("route('products.download'")
        ->toContain('frontend.components.content-card');

    expect($checkout)
        ->toContain("route('payments.pay',[\$type,\$item->public_id])")
        ->toContain("route('eula')");

    expect($related)->toContain("route('products.show'");
});

it('filters public related content at the relation owner level', function () {
    $productRepo = file_get_contents(base_path('app/Repositories/Eloquent/ProductRepository.php'));
    $articleRepo = file_get_contents(base_path('app/Repositories/Eloquent/ArticleRepository.php'));
    $recipeRepo = file_get_contents(base_path('app/Repositories/Eloquent/RecipeRepository.php'));
    $referenceRepo = file_get_contents(base_path('app/Repositories/Eloquent/ReferenceRepository.php'));

    expect($productRepo)
        ->toContain("where('articles.is_visible', true)")
        ->toContain("whereIn('recipes.visibility_status', ['demo', 'published'])")
        ->toContain("where('ingredients.is_visible', true)");

    expect($articleRepo)->toContain("where('products.is_visible',true)");
    expect($recipeRepo)->toContain("where('products.is_visible',true)");
    expect($referenceRepo)->toContain("where('products.is_visible',true)");
});

it('reuses one related-product component across selling content surfaces', function () {
    foreach ([
        'resources/web/frontend/catalog/article.blade.php',
        'resources/web/frontend/catalog/recipe.blade.php',
        'resources/web/frontend/catalog/ingredient.blade.php',
    ] as $view) {
        expect(file_get_contents(base_path($view)))
        ->toContain('frontend.components.related-products');
    }
});
