@extends('frontend.layouts.public')

@section('title', 'Поиск — Лика Вуле')

@section('content')
<section class="catalog-hero">
    <div class="container narrow">
        <span class="eyebrow">Поиск</span>
        <h1>Что приготовим сегодня?</h1>
        <form class="site-search d-flex mt-4" action="{{ route('search') }}" method="get">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input name="q" value="{{ $q }}" placeholder="Например, блины, мука или хлеб…" maxlength="120">
            <button class="btn btn-brand">Найти</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        @if($q === '')
            <div class="empty-state"><h3>Введите запрос</h3><p>Ищем по рецептам, статьям и справочнику ингредиентов.</p></div>
        @else
            <h2 class="mb-4">Результаты для «{{ $q }}»</h2>
            <div class="row g-4">
                @foreach($recipes as $item)
                    <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Рецепт','category'=>$item->categories->first()?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('recipes.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->publish_at ?? $item->created_at]])</div>
                @endforeach
                @foreach($articles as $item)
                    <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Статья','category'=>$item->category?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('articles.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->publish_at ?? $item->created_at]])</div>
                @endforeach
                @foreach($ingredients as $item)
                    <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Справочник','category'=>$item->category?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('ingredients.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->created_at]])</div>
                @endforeach
            </div>
            @if($articles->isEmpty() && $recipes->isEmpty() && $ingredients->isEmpty())
                <div class="empty-state mt-4"><h3>Ничего не нашли</h3><p>Попробуйте чуть короче или используйте другое слово.</p></div>
            @endif
        @endif
    </div>
</section>
@endsection
