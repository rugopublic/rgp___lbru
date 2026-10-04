@extends('frontend.layouts.public')

@section('title', $course->title.' — Лика Вуле')
@section('description', $course->seo?->meta_description ?: ($course->summary ?: \Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish(strip_tags((string) $course->objective)), 160) ?: 'Курс Лики Вуле'))

@section('content')
<section class="course-hero course-hero--sales">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">{{ $course->category?->name ?? 'Курс' }}</span>
                <h1>{{ $course->title }}</h1>

                @if($course->summary)
                    <p class="lead mb-4">{{ $course->summary }}</p>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @auth
                        @if($enrolled)
                            <a class="btn btn-brand btn-lg" href="{{ route('courses.learn', $course->public_id) }}">
                                Продолжить обучение
                            </a>
                        @else
                            <a class="btn btn-brand btn-lg" href="{{ route('courses.buy', $course->public_id) }}">
                                Получить доступ за {{ number_format((float) $course->price, 0, ',', ' ') }} ₽
                            </a>
                        @endif
                    @else
                        <a class="btn btn-brand btn-lg" href="{{ route('auth.login.form') }}?redirect={{ urlencode(route('courses.buy', $course->public_id)) }}">
                            Войти и купить
                        </a>
                    @endauth
                    <a class="btn btn-outline-secondary btn-lg" href="#program">Посмотреть программу</a>
                </div>

                @if($course->difficulty)
                    <div class="mt-3 text-muted small">Уровень: {{ $course->difficulty->value }}</div>
                @endif
            </div>

            <div class="col-lg-5">
                @if($course->cover)
                    <img class="w-100 rounded-4 shadow-sm" src="{{ $course->cover_url }}" alt="{{ $course->title }}">
                @else
                    <div class="sale-block h-100 d-flex align-items-center justify-content-center text-center">
                        <div>
                            <span class="eyebrow">Лика Вуле</span>
                            <strong class="d-block mt-2">Курс, к которому хочется вернуться</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@if($course->pitch)
<section class="section">
    <div class="container narrow">
        <div class="rich-content course-pitch">{!! $course->pitch !!}</div>
    </div>
</section>
@endif

<section class="section section--warm">
    <div class="container">
        <div class="section-head mb-4">
            <div>
                <span class="eyebrow">Зачем это вам</span>
                <h2>Ценность курса — в результате</h2>
            </div>
        </div>

        <div class="row g-3">
            @if($course->objective)
                <div class="col-md-6 col-lg-4">
                    <article class="sale-card h-100">
                        <span class="eyebrow">Задача</span>
                        <h3 class="h5 mt-2">Что изменится</h3>
                        <div class="rich-content mb-0">{!! $course->objective !!}</div>
                    </article>
                </div>
            @endif
            @if($course->skills)
                <div class="col-md-6 col-lg-4">
                    <article class="sale-card h-100">
                        <span class="eyebrow">Навык</span>
                        <h3 class="h5 mt-2">Что вы будете уметь</h3>
                        <div class="rich-content mb-0">{!! $course->skills !!}</div>
                    </article>
                </div>
            @endif
            @if($course->features)
                <div class="col-md-6 col-lg-4">
                    <article class="sale-card h-100">
                        <span class="eyebrow">Формат</span>
                        <h3 class="h5 mt-2">Как устроено обучение</h3>
                        <p class="mb-0">{{ $course->features }}</p>
                    </article>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 align-items-start">
            @if($course->audience)
                <div class="col-lg-6">
                    <div class="sale-block h-100">
                        <span class="eyebrow">Кому подходит</span>
                        <h2 class="h4 mt-2">Для кого этот курс</h2>
                        <p class="mb-0">{{ $course->audience }}</p>
                    </div>
                </div>
            @endif
            @if($course->prerequisites || $course->preferables)
                <div class="col-lg-6">
                    <div class="sale-block h-100">
                        <span class="eyebrow">Перед стартом</span>
                        <h2 class="h4 mt-2">Что важно знать заранее</h2>
                        @if($course->prerequisites)
                            <p class="mb-2"><strong>Необходимо:</strong> {{ $course->prerequisites }}</p>
                        @endif
                        @if($course->preferables)
                            <p class="mb-0"><strong>Желательно:</strong> {{ $course->preferables }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="section section--warm" id="program">
    <div class="container narrow">
        <div class="section-head mb-4">
            <div>
                <span class="eyebrow">Содержание</span>
                <h2>Внутри курса</h2>
            </div>
            <span class="text-muted">{{ $course->lessons->count() }} уроков</span>
        </div>

        @forelse($course->modules as $module)
            <article class="module-card mb-3">
                <div class="module-card__head">
                    <div>
                        <span>Модуль {{ $loop->iteration }}</span>
                        <h3>{{ $module->title }}</h3>
                    </div>
                    <span>{{ $module->lessons->count() }} уроков</span>
                </div>
                <ol class="mb-0">
                    @foreach($module->lessons as $lesson)
                        <li class="py-3 d-flex justify-content-between align-items-start gap-3 border-bottom">
                            <div>
                                <strong>{{ $lesson->title }}</strong>
                                @if($lesson->description)
                                    <div class="text-muted small mt-1">{{ $lesson->description }}</div>
                                @endif
                            </div>
                            <span class="small text-muted text-nowrap">
                                {{ $lesson->duration_minutes ? $lesson->duration_minutes.' мин' : '' }}
                                @if($lesson->is_preview)
                                    <span class="badge rounded-pill text-bg-light ms-1">preview</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ol>
            </article>
        @empty
            <div class="empty-state">
                <h3>Программа ещё собирается</h3>
                <p>Содержание появится здесь до открытия продаж.</p>
            </div>
        @endforelse
    </div>
</section>

@if($course->teachers->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-head mb-4">
            <div>
                <span class="eyebrow">Авторы</span>
                <h2>Кто ведёт курс</h2>
            </div>
        </div>

        <div class="row g-3">
            @foreach($course->teachers as $teacher)
                <div class="col-md-6">
                    <article class="teacher-card h-100">
                        @if($teacher->avatar)
                            <img src="{{ $teacher->avatar_url }}" alt="{{ $teacher->title }}" loading="lazy">
                        @endif
                        <div>
                            <strong>{{ $teacher->title }}</strong>
                            @if($teacher->motto)
                                <div class="mt-1">{{ $teacher->motto }}</div>
                            @endif
                            @if($teacher->about)
                                <p class="text-muted mt-2 mb-0">{{ $teacher->about }}</p>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section section--warm">
    <div class="container narrow">
        <div class="purchase-strip purchase-strip--large">
            <div>
                <span class="eyebrow">Стоимость</span>
                <h2 class="h3 mt-2 mb-1">Полный доступ к курсу</h2>
                <p class="mb-0 text-muted">Оплата проходит через ЮKassa. Доступ выдаётся автоматически после подтверждения платежа.</p>
            </div>
            <div class="text-end">
                <strong class="price d-block mb-2">{{ number_format((float) $course->price, 0, ',', ' ') }} ₽</strong>
                @auth
                    @if($enrolled)
                        <a class="btn btn-brand btn-lg" href="{{ route('courses.learn', $course->public_id) }}">Открыть курс</a>
                    @else
                        <a class="btn btn-brand btn-lg" href="{{ route('courses.buy', $course->public_id) }}">Получить доступ</a>
                    @endif
                @else
                    <a class="btn btn-brand btn-lg" href="{{ route('auth.login.form') }}">Войти и купить</a>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection
