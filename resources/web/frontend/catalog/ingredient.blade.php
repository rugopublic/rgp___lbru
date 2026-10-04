@extends('frontend.layouts.public')

@section('title', $ingredient->title.' — Справочник')

@section('content')
<section class="detail-hero">
    <div class="container narrow">
        <span class="eyebrow">Справочник</span>
        <h1>{{ $ingredient->title }}</h1>
        @if($ingredient->description)
            <p class="lead">{{ $ingredient->description }}</p>
        @endif
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <div class="row g-3">
            @foreach([
                'Белки' => $ingredient->protein,
                'Жиры' => $ingredient->fat,
                'Углеводы' => $ingredient->carbs,
                'Ккал' => $ingredient->kcal,
            ] as $label => $value)
                <div class="col-md-3">
                    <div class="admin-kpi">
                        <small>{{ $label }}</small>
                        <strong>{{ $value ?? '—' }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        @if($ingredient->body)
            <article class="rich-content mt-5">{!! $ingredient->body !!}</article>
        @endif

        @include('frontend.components.related-products', ['products' => $ingredient->products])

        <div class="mt-5 interaction-row">
            @include('frontend.components.interactions', [
                'model' => $ingredient,
                'type' => 'ingredient',
                'likeCount' => $interaction['likeCount'],
                'liked' => $interaction['liked'],
                'saved' => $interaction['saved'],
            ])
        </div>

        <section class="comments mt-5" id="comments" aria-labelledby="ingredient-comments-title">
            <h2 id="ingredient-comments-title">Комментарии</h2>
            @include('frontend.components.comments', [
                'model' => $ingredient,
                'type' => 'ingredient',
                'comments' => $comments,
            ])
        </section>
    </div>
</section>
@endsection
