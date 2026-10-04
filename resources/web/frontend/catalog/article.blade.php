@extends('frontend.layouts.public')

@section('title', $article->title.' — Лика Вуле')

@section('content')
<section class="detail-hero">
    <div class="container narrow">
        <span class="eyebrow">{{ $article->category?->name ?? 'Статья' }}</span>
        <h1>{{ $article->title }}</h1>
        @if($article->description)
            <p class="lead">{{ $article->description }}</p>
        @endif
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <article class="rich-content">{!! $article->body !!}</article>

        @include('frontend.components.related-products', ['products' => $article->products])

        <div class="mt-5 interaction-row">
            @include('frontend.components.interactions', [
                'model' => $article,
                'type' => 'article',
                'likeCount' => $interaction['likeCount'],
                'liked' => $interaction['liked'],
                'saved' => $interaction['saved'],
            ])
        </div>

        <section class="comments mt-5" id="comments" aria-labelledby="article-comments-title">
            <h2 id="article-comments-title">Комментарии</h2>
            @include('frontend.components.comments', [
                'model' => $article,
                'type' => 'article',
                'comments' => $comments,
            ])
        </section>
    </div>
</section>
@endsection
