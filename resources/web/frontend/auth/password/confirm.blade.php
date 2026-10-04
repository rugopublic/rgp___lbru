{{--
|--------------------------------------------------------------------------
| Password Reset Confirmation Page
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.password.confirm
|
| Purpose:
|   Displayed after a guest submits a password reset request.
|   Provides neutral feedback that a reset link has been sent if the account exists.
|
| Data passed to the view:
|   - Optional: $email (partially masked) — may be shown for UX, but never full exposure
|
| Typical content:
|   - Neutral message: "If an account exists for the provided email, a reset link has been sent."
|   - Link/button: "Back to login"
|   - Optional guidance: check spam folder, wait a few minutes
|
| Flow context:
|   → User submits email on /auth/forgot page
|   → POST /auth/forgot triggers AuthPasswordResetService::send
|   → This view confirms request regardless of whether the email exists
|
| Security / UX:
|   - Neutral message prevents user enumeration
|   - Optional email masking if displayed
|   - Clear guidance for next steps
|   - CSRF handled in previous step
|
| Route relation:
|   POST /auth/forgot → ForgotPasswordController@send → render this view
--}}
@extends('frontend.layouts.auth')

@section('title', __('auth.password_reset.send.confirm_title'))

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
                        <h1 class="mb-2">{{ __('auth.password_reset.send.confirm_title') }}</h1>

                        {{-- Сообщение --}}
                        <p class="text-muted mb-4">
                            {{ __('auth.password_reset.send.success_message') }}
                            @isset($email)
                                <br>
                                {{ __('auth.password_reset.send.confirm_email', ['email' => $email]) }}
                            @endisset
                        </p>

                        {{-- Действие --}}
                        <div class="d-flex flex-column flex-md-row align-items-center gap-2">
                            <a href="{{ route('auth.login.form') }}" class="btn btn-secondary px-4">
                                {{ __('auth.links.back_to_login') }}
                            </a>
                        </div>

                        {{-- Дополнительная заметка --}}
                        <p class="text-muted mt-3 small">
                            {{ __('auth.password_reset.send.check_spam') }}
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
