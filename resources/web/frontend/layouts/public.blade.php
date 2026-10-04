<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Лика Вуле — рецепты для жизни')</title>
    <meta name="description" content="@yield('description', 'Рецепты, статьи, ингредиенты, курсы и домашние знания Лики Вуле.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fbfaf6">
    <link rel="icon" href="{{ asset('images/brand/likablog-logo.svg') }}" type="image/svg+xml">
    @vite(['resources/styles/frontend/app.scss','resources/scripts/frontend/app.js'])
    @stack('head')
</head>
<body class="public-site">
    @include('frontend.common.header')

    @if(session('interaction_status'))
        <div class="container pt-3">
            <div class="alert alert-soft-success mb-0">{{ session('interaction_status') }}</div>
        </div>
    @endif
    @if(session('payment_pending'))
        <div class="container pt-3">
            <div class="alert alert-soft-success mb-0">Переходим к оплате. После подтверждения ЮKassa курс автоматически появится в вашем кабинете.</div>
        </div>
    @endif

    <main>@yield('content')</main>

    @include('frontend.common.footer')
    @include('frontend.common.cookie-banner')
    @stack('scripts')
</body>
</html>
