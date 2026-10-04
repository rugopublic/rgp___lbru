@extends('frontend.layouts.public')
@section('title', 'Справочник — Лика Вуле')
@section('content')
<section class="catalog-hero">
    <div class="container narrow">
        <span class="eyebrow">Справочник</span>
        <h1>Ингредиенты с фактами, а не догадками.</h1>
        <p>Пищевая ценность, условия хранения и заметки для выбора — рядом с рецептами и товарами.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="row g-4">
            @forelse($ingredients as $ingredient)
                <div class="col-md-6 col-lg-4">
                    @include('frontend.components.content-card', [
                        'item' => [
                            'url' => route('ingredients.show', $ingredient->public_id),
                            'image' => $ingredient->cover_url,
                            'badge' => 'Справочник',
                            'category' => $ingredient->category?->name,
                            'title' => $ingredient->title,
                            'excerpt' => $ingredient->description ?: $ingredient->body,
                            'published_at' => $ingredient->created_at,
                        ],
                    ])
                </div>
            @empty
                <div class="col-12"><div class="admin-empty">Записей пока нет.</div></div>
            @endforelse
        </div>
        {{ $ingredients->links() }}
    </div>
</section>
@endsection
