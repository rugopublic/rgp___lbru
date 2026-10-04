{{--
|--------------------------------------------------------------------------
| Registration — Complete / Success
|--------------------------------------------------------------------------
|
| View: web.frontend.register.complete
|
| Purpose:
|   Displayed after the user successfully sets their password via
|   the registration token link. Confirms that registration is complete
|   and provides a clear call-to-action to proceed to the profile/dashboard.
|
| Data passed to the view:
|   - Optional User object or email for reference
|   - Optional flash messages
|
| Typical content:
|   - Message: "Регистрация завершена! Добро пожаловать."
|   - Button/link: "Перейти в профиль" → GET /user/dashboard
|
| Flow context:
|   → User submits password via PUT /register
|   → RegisterController@update handles token validation and password setup
|   → Registration is finalized → render this view
|
| Security/UX:
|   - Friendly, neutral confirmation
|   - Provides next step clearly
|   - Avoids revealing sensitive information
|
| Route relation:
|   PUT /register → RegisterController@update → render web.frontend.register.complete
--}}

@extends('frontend.layouts.auth')

@section('title', __('register.complete.title'))

@section('content')
    <div class="container">
        <div class="row justify-content-center g-4">

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

                        <header class="mb-4">
                            <h1 class="mb-2">{{ __('register.complete.title') }}</h1>
                            <p class="text-muted">{{ __('register.complete.subtitle') }}</p>
                        </header>

                        <p class="mb-4">
                            {{ __('register.complete.message') }}
                        </p>

                        <div class="d-flex justify-content-start">
                            <a href="{{ route('auth.login.form') }}" class="btn btn-primary px-4">
                                {{ __('register.complete.go_to_profile') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
