<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', config('app.name'))
    </title>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO / UX base --}}
    <meta name="robots" content="noindex, nofollow">

    {{-- Styles --}}
    {{-- Vite / CSS --}}
    @vite(['resources/styles/frontend/app.scss','resources/scripts/frontend/app.js'])

    @stack('head')
</head>
<body class="layout layout--auth">

<header class="layout__header">
    @include('frontend.common.header')
</header>

<main class="layout__content">
    <div class="container">
        <div class="row g-4">
            {{-- Основной контент --}}
            <div class="col-12 col-md-8">
                @yield('content')
            </div>

            {{-- Боковая панель --}}
            <aside class="col-12 col-md-4">
                @yield('sidebar')
            </aside>
        </div>
    </div>
</main>

{{-- Optional footer --}}
<footer class="layout__footer">
    <small>
        © {{ now()->year }} {{ config('app.name') }}
    </small>
</footer>

@stack('scripts')
</body>
</html>
