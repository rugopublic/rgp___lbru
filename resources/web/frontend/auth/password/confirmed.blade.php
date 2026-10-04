{{--
|--------------------------------------------------------------------------
| Password Reset — Confirmed & Reset Form
|--------------------------------------------------------------------------
|
| View: web.frontend.auth.password.confirmed
|
| Purpose:
|   Displayed when the user clicks a valid password reset link.
|   Allows user to immediately set a new password.
|
| Data passed to the view:
|   - $token — the valid password reset token
|
| Typical content:
|   - Message: "Введите новый пароль для вашей учётной записи"
|   - Form fields: password, password confirmation, hidden token
|   - Submit button: "Сменить пароль"
|
| Flow context:
|   → User clicks link from password reset email
|   → Token is valid and not expired
|   → User sets a new password here
|
| Security/UX:
|   - Do not display full email
|   - CSRF protection
|   - Show validation errors if submission fails
|
| Route relation:
|   POST /auth/password/reset → handled by ForgotPasswordController@update
--}}
@extends('frontend.layouts.auth')

@section('title', __('auth.password_reset.edit.title'))

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
                        <h1 class="mb-2">{{ __('auth.password_reset.edit.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('auth.password_reset.edit.subtitle') }}</p>

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

                        <form action="{{ route('auth.forgot.update') }}" method="POST" novalidate>
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="token" value="{{ request()->route('token') }}">

                            {{-- Новый пароль --}}
                            <div class="mb-3">
                                <label for="p_input" class="form-label">
                                    {{ __('auth.password_reset.edit.password_label') }}
                                </label>
                                <input
                                    type="password"
                                    id="p_input"
                                    name="p_input"
                                    required
                                    class="form-control @error('p_input') is-invalid @enderror"
                                    placeholder="••••••••"
                                >
                                @error('p_input')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Подтверждение пароля --}}
                            <div class="mb-3">
                                <label for="p_repeat" class="form-label">
                                    {{ __('auth.password_reset.edit.password_confirmation_label') }}
                                </label>
                                <input
                                    type="password"
                                    id="p_repeat"
                                    name="p_repeat"
                                    required
                                    class="form-control"
                                    placeholder="••••••••"
                                >
                            </div>

                            {{-- Кнопка действия --}}
                            <div class="d-flex flex-column flex-md-row align-items-center gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('auth.password_reset.edit.submit') }}
                                </button>
                            </div>

                            {{-- Примечание --}}
                            <p class="text-muted mt-3 small">
                                {{ __('auth.password_reset.edit.note') }}
                            </p>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
