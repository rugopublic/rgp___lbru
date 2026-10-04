@extends('frontend.layouts.public')
@section('title', 'Рецепты — Лика Вуле')
@section('content')
<section class="catalog-hero">
    <div class="container narrow">
        <span class="eyebrow">Рецепты</span>
        <h1>Рецепты, которые хочется готовить, а не просто сохранять.</h1>
        <p>Ингредиенты, последовательность и детали — в одном понятном месте.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="row g-4">
            @forelse($recipes as $recipe)
                <div class="col-md-6 col-lg-4">
                    @include('frontend.components.content-card', [
                        'item' => [
                            'url' => route('recipes.show', $recipe->public_id),
                            'image' => $recipe->cover_url,
                            'badge' => 'Рецепт',
                            'category' => $recipe->categories->first()?->name,
                            'title' => $recipe->title,
                            'excerpt' => $recipe->description ?: $recipe->body,
                            'published_at' => $recipe->publish_at ?: $recipe->created_at,
                        ],
                    ])
                </div>
            @empty
                <div class="col-12"><div class="admin-empty">Рецептов пока нет.</div></div>
            @endforelse
        </div>
        {{ $recipes->links() }}
    </div>
</section>
@endsection
