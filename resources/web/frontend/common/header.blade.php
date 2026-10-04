<header class="site-header sticky-top">
    <div class="container">
        <div class="site-header__top">
            <a href="{{ route('home') }}" class="brand brand--logo" aria-label="Лика Вуле">
                <img src="{{ asset('images/brand/likablog-logo.svg') }}" alt="Лика Вуле — Рецепты для жизни">
            </a>
            <form class="site-search d-none d-lg-flex" action="{{ route('search') }}" method="get">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Найти рецепт, продукт, статью или курс…" autocomplete="off">
                <kbd>⌘K</kbd>
            </form>
            <div class="site-header__actions">
                @auth
                    <a class="icon-btn" href="{{ route('user.account.index') }}"><i class="bi bi-person-circle"></i><span class="d-none d-md-inline">Мой профиль</span></a>
                @else
                    <a class="icon-btn" href="{{ route('auth.login.form') }}"><i class="bi bi-box-arrow-in-right"></i><span class="d-none d-md-inline">Войти</span></a>
                    <a class="btn btn-brand" href="{{ route('register.form') }}">Присоединиться</a>
                @endauth
            </div>
        </div>
        <nav class="site-nav">
            <a href="{{ route('home') }}"><i class="bi bi-house-heart me-1"></i>Главная</a>
            <a href="{{ route('recipes.index') }}">Рецепты</a>
            <a href="{{ route('articles.index') }}">Статьи</a>
            <a href="{{ route('ingredients.index') }}">Справочник</a>
            <a href="{{ route('courses.index') }}">Курсы</a>
            <a href="{{ route('products.index') }}">Магазин</a>
            <a href="{{ route('about') }}">О Лике</a>
            <a href="{{ route('help.index') }}" class="ms-lg-auto"><i class="bi bi-life-preserver me-1"></i>Помощь</a>
        </nav>
    </div>
</header>
