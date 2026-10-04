@extends('frontend.layouts.public')

@section('title','Мои покупки — Лика Вуле')

@section('content')
<section class="catalog-hero">
    <div class="container narrow">
        <span class="eyebrow">История и доступ</span>
        <h1>Мои покупки</h1>
        <p>Здесь можно проверить оплату и сразу перейти к купленному материалу.</p>
    </div>
</section>
<section class="section">
    <div class="container narrow">
        @forelse($purchases as $purchase)
            @php($item = $purchase->paymentIntent?->purchasable)
            <article class="purchase-row purchase-row--stacked">
                <div class="d-flex flex-column gap-1">
                    <strong>Заказ #{{ $purchase->id }}</strong>
                    <span>{{ optional($purchase->created_at)->translatedFormat('j F Y, H:i') }} · {{ $purchase->status }}</span>
                    @if($item)
                        <span class="small text-muted">{{ $item->title ?? $item->name }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <strong>{{ number_format((float) $purchase->amount,2,',',' ') }} ₽</strong>
                    @if($item instanceof \App\Models\Product && $purchase->status === 'completed')
                        <a class="btn btn-brand btn-sm" href="{{ route('products.download', $item->public_id) }}">Получить файл</a>
                    @elseif($item instanceof \App\Models\Course && $purchase->status === 'completed')
                        <a class="btn btn-brand btn-sm" href="{{ route('courses.learn', $item->public_id) }}">Открыть курс</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-state"><h3>Покупок пока нет</h3><p>Выберите материал, который действительно пригодится, и он появится здесь.</p><a class="btn btn-brand" href="{{ route('products.index') }}">Посмотреть материалы</a></div>
        @endforelse
        {{ $purchases->links() }}
    </div>
</section>
@endsection
