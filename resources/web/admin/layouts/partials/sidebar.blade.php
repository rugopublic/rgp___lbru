<div class="sidebar-logo">
    <h3><i class="bi bi-grid-3x3-gap-fill me-2"></i>{{ config('app.name') }}<br><span>администрирование</span></h3>
</div>
<nav class="sidebar-nav" aria-label="Основная навигация">
    <div class="sidebar-section">Производство</div>
    <ul class="nav flex-column">
        @foreach([
            ['dashboard','admin.dashboard','speedometer2','Дашборд'],
            ['calendar','admin.calendar.index','calendar3','Календарь'],
            ['transactions','admin.transactions.index','receipt','Транзакции'],
        ] as [$key,$route,$icon,$label])
        <li class="nav-item"><a class="nav-link @if(($activeMenu??'')===$key) active @endif" href="{{ route($route) }}"><i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span></a></li>
        @endforeach
    </ul>
    <div class="sidebar-section">Контент</div>
    <ul class="nav flex-column">
        @foreach([
            ['articles','admin.articles.index','newspaper','Статьи'],['recipes','admin.recipes.index','book-half','Рецепты'],['reference','admin.reference.index','journal-bookmark','Справочник'],['products','admin.products.index','box-seam','Товары'],['courses','admin.courses.index','mortarboard','Курсы'],['teachers','admin.teachers.index','person-badge','Преподаватели'],
        ] as [$key,$route,$icon,$label])
        <li class="nav-item"><a class="nav-link @if(($activeMenu??'')===$key) active @endif" href="{{ route($route) }}"><i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span></a></li>
        @endforeach
    </ul>
    <div class="sidebar-section">Доступ</div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link @if(($activeMenu??'')==='users') active @endif" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i><span>Пользователи</span></a></li>
        <li class="nav-item"><a class="nav-link @if(($activeMenu??'')==='groups') active @endif" href="{{ route('admin.groups.index') }}"><i class="bi bi-diagram-3"></i><span>Группы</span></a></li>
    </ul>
</nav>
<div class="sidebar-user"><div class="avatar"><i class="bi bi-person"></i></div><div class="min-w-0"><strong class="d-block text-truncate">{{ auth()->user()->account?->name ?: auth()->user()->email }}</strong><span class="text-muted small text-truncate d-block">{{ auth()->user()->email }}</span></div></div>
