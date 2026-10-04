@extends('frontend.layouts.public')
@section('title', $product->title.' — Магазин Лики Вуле')
@section('description', $product->seo?->meta_description ?: ($product->description ?: $product->title))
@section('content')
<section class="detail-hero">
    <div class="container narrow">
        <span class="eyebrow">{{ $product->category?->name ?? 'Материал' }}</span>
        <h1>{{ $product->title }}</h1>
        @if($product->description)<p class="lead">{{ $product->description }}</p>@endif
    </div>
</section>
<section class="section">
    <div class="container narrow">
        <div class="purchase-strip">
            <div>
                <span class="eyebrow">Одноразовая покупка</span>
                <h2 class="price">{{ number_format((float) $product->price, 0, ',', ' ') }} ₽</h2>
                <p class="mb-0">Файл хранится приватно. После подтверждённой оплаты доступ появляется в профиле автоматически.</p>
            </div>
            <div class="text-end">
                @auth
                    @if($owned)
                        <a href="{{ route('products.download', $product->public_id) }}" class="btn btn-brand btn-lg">Получить файл</a>
                        <div class="small text-muted mt-2">Покупка уже активна</div>
                    @else
                        <a href="{{ route('products.buy', $product->public_id) }}" class="btn btn-brand btn-lg">Купить</a>
                    @endif
                @else
                    <a href="{{ route('auth.login.form') }}?redirect={{ urlencode(route('products.buy', $product->public_id)) }}" class="btn btn-brand btn-lg">Войти и купить</a>
                @endauth
            </div>
        </div>

        @if($product->cover_url)
            <div class="mt-5"><img class="w-100 rounded-4 shadow-sm" src="{{ $product->cover_url }}" alt="{{ $product->title }}" loading="lazy"></div>
        @endif

        <div class="mt-5 interaction-row">
            @include('frontend.components.interactions', ['model'=>$product,'type'=>'product','likeCount'=>$interaction['likeCount'],'liked'=>$interaction['liked'],'saved'=>$interaction['saved']])
        </div>

        @if($product->articles->isNotEmpty() || $product->recipes->isNotEmpty() || $product->referenceItems->isNotEmpty())
            <section class="section section--warm mt-5">
                <div class="section-head mb-4"><div><span class="eyebrow">Контекст</span><h2>Где этот материал пригодится</h2></div></div>
                <div class="row g-3">
                    @foreach($product->articles as $item)
                        <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Статья','category'=>$item->category?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('articles.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->publish_at ?? $item->created_at]])</div>
                    @endforeach
                    @foreach($product->recipes as $item)
                        <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Рецепт','category'=>$item->categories->first()?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('recipes.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->publish_at ?? $item->created_at]])</div>
                    @endforeach
                    @foreach($product->referenceItems as $item)
                        <div class="col-md-6">@include('frontend.components.content-card', ['item'=>['badge'=>'Справочник','category'=>$item->category?->name,'title'=>$item->title,'excerpt'=>$item->description,'url'=>route('ingredients.show',$item->public_id),'image'=>$item->cover_url,'published_at'=>$item->created_at]])</div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="comments mt-5" id="comments" aria-labelledby="comments-title">
            <h2 id="comments-title">Комментарии</h2>
            @include('frontend.components.comments', ['model'=>$product,'type'=>'product','comments'=>$comments])
        </section>
    </div>
</section>
@endsection
