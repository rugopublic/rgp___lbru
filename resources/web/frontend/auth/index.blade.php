@extends('frontend.layouts.auth')

@section('title', 'Вход — Лика Вуле')

@section('content')
<section class="auth-choice py-5">
    <div class="container narrow">
        <div class="text-center mb-5">
            <span class="eyebrow">Лика Вуле</span>
            <h1 class="display-5 mt-2">Войдите, чтобы продолжить</h1>
            <p class="lead text-muted mx-auto" style="max-width: 42rem;">
                Сохраните покупки, доступ к курсам и свой личный прогресс в одном профиле.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-6">
                <article class="auth-choice-card h-100">
                    <span class="eyebrow">Уже с нами</span>
                    <h2 class="h3 mt-2">Войти в аккаунт</h2>
                    <p class="text-muted">Используйте email и пароль, чтобы открыть свои покупки и обучение.</p>
                    <a class="btn btn-brand btn-lg w-100" href="{{ route('auth.login.form', ['redirect' => request('redirect')]) }}">Войти</a>
                </article>
            </div>
            <div class="col-md-6">
                <article class="auth-choice-card h-100">
                    <span class="eyebrow">Новый пользователь</span>
                    <h2 class="h3 mt-2">Создать аккаунт</h2>
                    <p class="text-muted">Оставьте email — после подтверждения адреса вы зададите пароль и получите доступ к профилю.</p>
                    <a class="btn btn-outline-secondary btn-lg w-100" href="{{ route('register.form') }}">Зарегистрироваться</a>
                </article>
            </div>
        </div>

        <p class="small text-muted text-center mt-4 mb-0">
            Продолжая, вы принимаете <a href="{{ route('eula') }}">условия использования</a> и знакомитесь с <a href="{{ route('pp') }}">политикой конфиденциальности</a>.
        </p>
    </div>
</section>
@endsection
