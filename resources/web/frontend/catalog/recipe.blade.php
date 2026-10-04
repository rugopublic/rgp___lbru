@extends('frontend.layouts.public')

@section('title', $recipe->title.' — Лика Вуле')

@section('content')
<section class="detail-hero">
    <div class="container narrow">
        <span class="eyebrow">Рецепт</span>
        <h1>{{ $recipe->title }}</h1>
        @if($recipe->description)
            <p class="lead">{{ $recipe->description }}</p>
        @endif
    </div>
</section>

<section class="section">
    <div class="container narrow">
        @if($recipe->body)
            <article class="rich-content">{!! $recipe->body !!}</article>
        @endif

        <div class="mt-5">
            <h2>Ингредиенты</h2>
            @forelse($recipe->ingredientGroups as $group)
                <section class="mb-4">
                    <h3 class="h6">{{ $group->title }}</h3>
                    <ul>
                        @foreach($group->ingredients as $ingredient)
                            <li>{{ $ingredient->referenceItem?->title }} — {{ $ingredient->quantity }} {{ $ingredient->unit }}</li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <p class="muted">Ингредиенты пока не добавлены.</p>
            @endforelse
        </div>

        @include('frontend.components.related-products', ['products' => $recipe->products])

        <div class="mt-5 interaction-row">
            @include('frontend.components.interactions', [
                'model' => $recipe,
                'type' => 'recipe',
                'likeCount' => $interaction['likeCount'],
                'liked' => $interaction['liked'],
                'saved' => $interaction['saved'],
            ])
        </div>

        <section class="comments mt-5" id="comments" aria-labelledby="recipe-comments-title">
            <h2 id="recipe-comments-title">Комментарии</h2>
            @include('frontend.components.comments', [
                'model' => $recipe,
                'type' => 'recipe',
                'comments' => $comments,
            ])
        </section>
    </div>
</section>
@endsection
