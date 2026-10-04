<div class="admin-toolbar">
    <form method="GET" class="admin-toolbar flex-grow-1">
        <div class="flex-grow-1" style="min-width:220px"><label class="form-label small text-muted">Поиск</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Название, email..."></div>
        {{ $slot }}
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Найти</button>
        @if(request()->hasAny(['search','visible','deleted','status','category','group','schedule']))<a class="btn btn-light border" href="{{ url()->current() }}">Сбросить</a>@endif
    </form>
</div>
