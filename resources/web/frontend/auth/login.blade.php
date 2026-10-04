{{--
|--------------------------------------------------------------------------
| Login Form Page View
|--------------------------------------------------------------------------
|
| This view displays the login form for guests.
| Accessible only to unauthenticated users.
|
| Key routes:
| - POST /auth/login → auth.login.attempt
|
| Middleware:
| - 'web'
| - 'guest'
|
| Typical content:
| - Email and password fields
| - Remember Me checkbox (optional)
| - Submit button
| - Display validation errors using $errors
|
| Security notes:
| - Do not indicate if the email exists in the system
| - All error messages are neutral
|
| UX notes:
| - Autofocus first field
| - Keep form simple and responsive
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('auth.login.title'))

@section('content')
    <div class="container">
        <div class="row align-items-center g-4">
            {{-- Баннер --}}
            <div class="col-12 col-md-4">
                <div class="ratio ratio-1x1">
                    <img
                        src="{{ asset('images/banners/auth/login.png') }}"
                        alt=""
                        role="presentation"
                        class="img-fluid object-fit-cover rounded"
                        loading="lazy"
                    >
                </div>
            </div>

            {{-- Контент --}}
            <div class="col-12 col-md-8">
                <div class="card border-0 bg-transparent">
                    <div class="card-body px-0">

                        {{-- Заголовок --}}
                        <h1 class="mb-2">{{ __('auth.login.header') }}</h1>
                        <p class="text-muted mb-4">{{ __('auth.login.subheader') }}</p>

                        {{-- Ошибки --}}
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('auth.login.attempt') }}" novalidate>
                            @if(request('redirect'))<input type="hidden" name="redirect" value="{{ request('redirect') }}">@endif
                            @csrf

                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('auth.email') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    placeholder="your@example.com"
                                    class="form-control @error('email') is-invalid @enderror"
                                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                >
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('auth.password') }}</label>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Введите пароль"
                                    class="form-control @error('password') is-invalid @enderror"
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                >
                            </div>

                            {{-- Remember --}}
                            <div class="form-check mb-3">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    value="1"
                                    class="form-check-input"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="remember">{{ __('auth.remember') }}</label>
                            </div>

                            {{-- Действие + вторичное действие --}}
                            <div class="d-flex flex-column flex-md-row align-items-center gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('auth.login.button') }}
                                </button>

                                <a href="{{ route('auth.forgot.form') }}" class="btn btn-secondary">
                                    {{ __('auth.login.button_forgot')  }}
                                </a>
                            </div>

                            <div class="d-flex mt-4">
                                {{ __('auth.login.no_account') }}&nbsp;
                                <a href="{{ route('register.form') }}">{{ __('auth.login.register') }}</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
