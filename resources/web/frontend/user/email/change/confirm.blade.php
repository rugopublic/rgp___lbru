{{--
|--------------------------------------------------------------------------
| User Email Change Confirmation Notice View
|--------------------------------------------------------------------------
|
| This view is displayed when a logged-in user confirms the email change
| on their old email address. Its purpose is to finalize or cancel
| the attachment of a new email.
|
| Key points:
| - The link contains a unique token.
| - The route is protected by "auth" middleware.
| - Clicking the link either applies the new email or cancels it
|   according to the workflow rules.
|
| Reference: Laravel 12.x Official Documentation
| Section: Authentication > Email Verification
| URL: https://laravel.com/docs/12.x/verification
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.email.confirm.title'))

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
                        <h1 class="mb-2">{{ __('user.account.email.confirm.heading') }}</h1>
                        <p class="text-muted mb-4">{{ __('user.account.email.confirm.subtitle') }}</p>

                        {{-- Сообщения --}}
                        @if(!empty($message))
                            <div class="alert alert-success mb-3">
                                {{ $message }}
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
                                {{ __('user.account.email.confirm.go_to_profile') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
