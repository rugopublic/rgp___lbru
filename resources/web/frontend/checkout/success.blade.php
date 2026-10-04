@extends('frontend.layouts.public')
@section('title', 'Статус оплаты — Лика Вуле')
@section('content')
<section class="section">
    <div class="container narrow">
        <div class="success-card">
            @if($intent->status === 'succeeded')
                <div class="success-card__icon"><i class="bi bi-check2"></i></div>
                <span class="eyebrow">Оплата подтверждена</span>
                <h1>Всё получилось</h1>
                <p>Платёж на <strong>{{ number_format($intent->amount, 0, ',', ' ') }} ₽</strong> принят. Если это был курс, доступ уже появился в вашем профиле.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('user.account.index') }}" class="btn btn-brand">Открыть профиль</a>
                    <a href="{{ route('home') }}" class="btn btn-quiet">Вернуться на главную</a>
                </div>
            @elseif($intent->status === 'canceled' || $intent->status === 'failed')
                <div class="success-card__icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <span class="eyebrow">Оплата не завершена</span>
                <h1>Давайте попробуем ещё раз</h1>
                <p>Платёж не был подтверждён. Вернитесь к товару или курсу и повторите попытку.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('user.account.index') }}" class="btn btn-brand">Открыть профиль</a>
                    <a href="{{ route('home') }}" class="btn btn-quiet">На главную</a>
                </div>
            @else
                <div class="success-card__icon"><i class="bi bi-hourglass-split"></i></div>
                <span class="eyebrow">Платёж проверяется</span>
                <h1>Ещё секундочку</h1>
                <p>ЮKassa ещё подтверждает платёж на <strong>{{ number_format($intent->amount, 0, ',', ' ') }} ₽</strong>. Обычно доступ появляется автоматически после подтверждения.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('payments.result', $intent->id) }}" class="btn btn-brand">Проверить статус</a>
                    <a href="{{ route('user.account.index') }}" class="btn btn-quiet">Открыть профиль</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
