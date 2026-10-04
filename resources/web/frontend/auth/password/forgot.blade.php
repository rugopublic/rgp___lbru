{{--
|--------------------------------------------------------------------------
| Password Reset Request Page
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.password.forgot
|
| Purpose:
|   Allows guest users to request a password reset link via email.
|
| Data passed to the view:
|   - None required by default.
|   - Old input may contain:
|       - email (string)
|
| Typical content:
|   - Email input field
|   - Submit button: "Send password reset link"
|   - Link back to login page
|
| Flow context:
|   → Guest opens the "Forgot password" page
|   → Submits email address
|   → Controller delegates request to AuthPasswordResetService
|   → User is redirected to a neutral confirmation page
|
| Security / UX notes:
|   - Do NOT reveal whether the email exists
|   - Always show neutral success feedback
|   - CSRF protection enabled
|   - Validation errors may be shown for malformed input
|
| Route relation:
|   GET  /auth/forgot           → ForgotPasswordController@form
|   POST /auth/password/email   → ForgotPasswordController@send
--}}
@extends('frontend.layouts.auth')

@section('title', __('auth.password_reset.send.title'))

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
                        <h1 class="mb-2">{{ __('auth.password_reset.send.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('auth.password_reset.send.description') }}</p>

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

                        <form method="POST" action="{{ route('auth.forgot.send') }}" novalidate>
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

                            {{-- Действие + вторичное действие --}}
                            <div class="d-flex flex-column flex-md-row align-items-center gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('auth.password_reset.send.submit') }}
                                </button>

                                <a href="{{ route('auth.login.form') }}" class="btn btn-secondary">
                                    {{ __('auth.links.back_to_login') }}
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
