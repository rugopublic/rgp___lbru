{{--
|--------------------------------------------------------------------------
| Password Reset — Success / Completed
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.password.reset
|
| Purpose:
|   Displayed after the user successfully resets their password.
|   Confirms that the password has been changed and provides next steps.
|
| Data passed to the view:
|   - Optional user email (partially masked) for reference
|
| Typical content:
|   - Message: "Ваш пароль был успешно изменён."
|   - Button/link: "Войти" → login page
|
| Flow context:
|   → User submits new password via password reset form
|   → Token is valid and password update succeeds
|   → Render this view to confirm success
|
| Security/UX:
|   - Neutral, friendly confirmation
|   - Clear call-to-action to log in
|
| Route relation:
|   POST /auth/forgot/{token} → password reset → render web.frontend.auth.password.reset
--}}
@extends('frontend.layouts.auth')

@section('title', __('auth.password_reset.reset.title'))

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
                        <h1 class="mb-2">{{ __('auth.password_reset.reset.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('auth.password_reset.reset.subtitle') }}</p>

                        {{-- Действие --}}
                        <a href="{{ route('auth.login.form') }}" class="btn btn-primary px-4">
                            {{ __('auth.password_reset.reset.login') }}
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
