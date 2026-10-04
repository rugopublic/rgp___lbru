@extends('admin.layouts.admin')

@section('title', 'Дашборд')
@section('page-title', 'Дашборд')
@section('admin-page', 'dashboard')

@section('content')
<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__head">
            <div>
                <span class="text-muted small">Производственный контур</span>
                <h2 class="h4 mb-1">Что требует внимания</h2>
                <div class="admin-section-note">Только сигналы, которые могут остановить выпуск или потребовать действия.</div>
            </div>
        </div>
        <div class="admin-surface__body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="admin-kpi h-100">
                        <small><i class="bi bi-pencil-square me-1"></i>Черновики</small>
                        <strong>{{ $attention['drafts'] }}</strong>
                        <span class="text-muted small">ещё не подготовлены к публикации</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-kpi h-100">
                        <small><i class="bi bi-exclamation-circle me-1"></i>Ошибки очереди</small>
                        <strong>{{ $attention['failed_jobs'] }}</strong>
                        <span class="text-muted small">проверить стандартными командами Laravel</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-kpi h-100">
                        <small><i class="bi bi-calendar-event me-1"></i>Отложка</small>
                        <strong>{{ $attention['scheduled'] }}</strong>
                        <span class="text-muted small">событий в ближайшие 14 дней</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="admin-surface h-100">
                <div class="admin-surface__head">
                    <div>
                        <h3 class="h6 mb-1">Ближайшая отложка</h3>
                        <div class="admin-section-note">Открывает тот же редактор, которым управляется публикация.</div>
                    </div>
                    <a href="{{ route('admin.calendar.index') }}" class="btn btn-outline-secondary btn-sm">Календарь</a>
                </div>
                <div class="admin-surface__body">
                    <div class="admin-list">
                        @forelse($events as $event)
                            <a class="admin-list-item text-decoration-none" href="{{ $event['url'] }}">
                                <div>
                                    <strong class="text-dark">{{ $event['title'] }}</strong>
                                    <div class="admin-list-item__meta">{{ $event['type'] }} · {{ $event['status'] }}</div>
                                </div>
                                <time class="small text-muted">{{ $event['published_at']->format('d.m H:i') }}</time>
                            </a>
                        @empty
                            <div class="admin-empty"><i class="bi bi-calendar2-check d-block mb-2"></i>Ближайших публикаций нет.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="admin-surface h-100">
                <div class="admin-surface__head">
                    <div>
                        <h3 class="h6 mb-1">Failed jobs</h3>
                        <div class="admin-section-note">Последние ошибки фоновых операций.</div>
                    </div>
                </div>
                <div class="admin-surface__body">
                    <div class="admin-list">
                        @forelse($failedJobs as $job)
                            <div class="admin-list-item">
                                <div>
                                    <strong class="text-dark">#{{ $job->id }} · {{ $job->queue ?: 'default' }}</strong>
                                    <div class="admin-list-item__meta">{{ \Illuminate\Support\Str::limit((string) $job->exception, 120) }}</div>
                                </div>
                                <time class="small text-muted">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('d.m H:i') }}</time>
                            </div>
                        @empty
                            <div class="admin-empty"><i class="bi bi-check-circle d-block mb-2"></i>Ошибок в очереди нет.</div>
                        @endforelse
                    </div>
                    @if($attention['failed_jobs'] > 0)
                        <p class="admin-section-note mt-3 mb-0"><code>php artisan queue:failed</code> → затем <code>queue:retry</code> или <code>queue:forget</code>.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <section class="admin-surface">
        <div class="admin-surface__head">
            <div>
                <h3 class="h6 mb-1">Последние изменения</h3>
                <div class="admin-section-note">Небольшой журнал последних правок контентных сущностей.</div>
            </div>
        </div>
        <div class="admin-surface__body">
            <div class="admin-list">
                @forelse($recentChanges as $change)
                    <a class="admin-list-item text-decoration-none" href="{{ $change['url'] }}">
                        <div>
                            <span class="admin-list-item__meta">{{ $change['label'] }}</span>
                            <strong class="text-dark d-block">{{ $change['title'] }}</strong>
                        </div>
                        <time class="small text-muted">{{ $change['updated_at']->format('d.m H:i') }}</time>
                    </a>
                @empty
                    <div class="admin-empty">Изменений пока нет.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
