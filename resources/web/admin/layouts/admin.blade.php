<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Админ-панель') · {{ config('app.name') }}</title>
    @vite(['resources/styles/admin/app.scss','resources/styles/admin/layout.scss','resources/scripts/admin/app.js'])
    @stack('styles')
</head>
<body data-admin-page="{{ trim($__env->yieldContent('admin-page')) ?: 'common' }}" data-admin-open-modal="{{ session('open_create_modal') }}">
<div class="app-wrapper">
    <aside class="sidebar d-none d-lg-flex">
        @include('admin.layouts.partials.sidebar')
    </aside>
    <main class="main-content">
        <header class="top-bar">
            <button class="btn-burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-label="Открыть меню"><i class="bi bi-list"></i></button>
            <div class="flex-grow-1"><div class="small text-muted">LIKABLOG.RU · Администрирование</div><h1 class="page-title">@yield('page-title')</h1></div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>На сайт</a>
        </header>
        <div class="content-body admin-page">
            @yield('content')
        </div>
    </main>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header"><div><div class="small text-muted">LIKABLOG.RU</div><h2 class="h6 mb-0" id="mobileSidebarLabel">Администрирование</h2></div><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button></div>
        <div class="offcanvas-body p-0">@include('admin.layouts.partials.sidebar')</div>
    </div>
</div>
<x-ui.form.admin-form-tools />
<x-ui.form.sender />
<x-ui.modal.form.deletion-consent />
<x-ui.modal.form.change-log />
@stack('scripts')
</body>
</html>
