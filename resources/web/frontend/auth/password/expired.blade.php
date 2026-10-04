{{--
|--------------------------------------------------------------------------
| Password Reset — Expired / Invalid Token
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.password.expired
|
| Purpose:
|   Displayed when a password reset link is invalid, expired, or already consumed.
|   Guides the user to re-request a new password reset link.
|
| Data passed to the view:
|   - Optional message explaining token status (default neutral)
|
| Typical content:
|   - Message: "Ссылка для сброса пароля недействительна или устарела."
|   - Button/link: "Запросить новую ссылку" → password reset request page
|
| Flow context:
|   → User clicks expired/invalid password reset link
|   → Render this view to prevent token reuse and guide next steps
|
| Security/UX:
|   - Neutral message to prevent user enumeration
|   - Clear call to action
|   - CSRF protection handled in subsequent form
|
| Route relation:
|   GET /auth/forgot/{token} → token invalid → render web.frontend.auth.password.expired
--}}
@extends('frontend.layouts.auth')

@section('title', __('auth.password_reset.expired.title'))

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
                        <h1 class="mb-2">{{ __('auth.password_reset.expired.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('auth.password_reset.expired.subtitle') }}</p>

                        {{-- Действие --}}
                        <a href="{{ route('auth.forgot.form') }}" class="btn btn-primary px-4">
                            {{ __('auth.password_reset.expired.request_new') }}
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
