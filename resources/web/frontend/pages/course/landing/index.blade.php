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

@include('frontend.pages.course.landing.01_hero')
@include('frontend.pages.course.landing.02_pain_points')
@include('frontend.pages.course.landing.03_change_points')
@include('frontend.pages.course.landing.04_content_promise')
@include('frontend.pages.course.landing.05_showcase')
@include('frontend.pages.course.landing.06_content')
@include('frontend.pages.course.landing.07_skills')
@include('frontend.pages.course.landing.08_target_audience')
@include('frontend.pages.course.landing.09_price')
@include('frontend.pages.course.landing.10_author')
@include('frontend.pages.course.landing.11_cta_block')

@stack('scripts')
</body>
</html>
