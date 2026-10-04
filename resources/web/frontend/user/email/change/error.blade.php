{{--
|--------------------------------------------------------------------------
| User Email Change Error View
|--------------------------------------------------------------------------
|
| This view is displayed when an error occurs during the email change
| verification process. It handles various failure scenarios such as:
| - Invalid or expired verification tokens
| - Cooldown period violations
| - Concurrent email change requests
| - System errors during processing
|
| Key points:
| - The view provides clear error messaging to the user
| - Offers recovery options (retry, contact support)
| - Maintains security by not exposing sensitive details
| - Uses consistent styling with other email change views
|
| Reference: Laravel 12.x Official Documentation
| Section: Authentication > Email Verification > Error Handling
| URL: https://laravel.com/docs/12.x/verification#error-handling
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.email.error.title'))

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

                        {{-- Заголовок --}}
                        <h1 class="mb-2">{{ __('user.account.email.error.heading') }}</h1>

                        {{-- Сообщения об ошибках --}}
                        @if(!empty($error))
                            <div class="alert alert-danger mb-3">
                                {{ $error }}
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-info mb-3">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{-- Кнопка "Перейти в профиль" --}}
                        <div class="d-flex justify-content-start gap-2 mt-4">
                            <a href="{{ route('user.account.index') }}" class="btn btn-primary px-4">
                                {{ __('user.account.email.error.go_to_profile') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
