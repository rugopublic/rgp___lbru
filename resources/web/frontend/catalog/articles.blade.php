@extends('frontend.layouts.public')
@section('title', 'Статьи — Лика Вуле')
@section('content')
<section class="catalog-hero">
    <div class="container narrow">
        <span class="eyebrow">Статьи</span>
        <h1>Мысли, объяснения и практические решения.</h1>
        <p>Материалы, к которым можно вернуться, чтобы понять контекст и сделать следующий шаг.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="row g-4">
            @forelse($articles as $article)
                <div class="col-md-6 col-lg-4">
                    @include('frontend.components.content-card', [
                        'item' => [
                            'url' => route('articles.show', $article->public_id),
                            'image' => $article->cover_url,
                            'badge' => 'Статья',
                            'category' => $article->category?->name,
                            'title' => $article->title,
                            'excerpt' => $article->description ?: $article->body,
                            'published_at' => $article->publish_at ?: $article->created_at,
                        ],
                    ])
                </div>
            @empty
                <div class="col-12"><div class="admin-empty">Статей пока нет.</div></div>
            @endforelse
        </div>
        {{ $articles->links() }}
    </div>
</section>
@endsection
