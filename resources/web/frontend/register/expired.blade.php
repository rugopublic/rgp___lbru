{{--
|--------------------------------------------------------------------------
| Registration — Token Expired / Invalid
|--------------------------------------------------------------------------
|
| View: web.frontend.register.expired
|
| Purpose:
|   Displayed when the user follows a registration link that is expired,
|   invalid, or already consumed.
|
| Data passed to the view:
|   - Optional flash messages
|
| Typical content:
|   - Message: "Ссылка недействительна или истекла."
|   - Button/link: "Запросить новую ссылку" → GET /register
|
| Flow context:
|   → User clicks tokenized email link
|   → RegisterController@verify checks token
|   → If token invalid/expired → render this view
|
| Security/UX:
|   - Neutral message, does not reveal if account exists
|   - Guides user to restart registration flow
|
| Route relation:
|   GET /register/{token} → RegisterController@verify → render web.frontend.register.expired
--}}

@extends('frontend.layouts.auth')

@section('title', __('register.expired.title'))

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
                            <h1 class="mb-2">{{ __('register.expired.title') }}</h1>
                            <p class="text-muted">{{ __('register.expired.subtitle') }}</p>
                        </header>

                        <div>
                            {{ __('register.expired.retry_text') }}
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <a href="{{ route('register.form') }}" class="btn btn-primary px-4">
                                {{ __('register.expired.retry') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
