{{--
|--------------------------------------------------------------------------
| Cookie Consent Error View
|--------------------------------------------------------------------------
|
| Informs the visitor that their cookie consent settings could not be updated.
| Provides a friendly message and a link to return to the settings page.
|
| Layout: web.frontend.layouts.main
| Localization: use __('cookies.*') keys
|
--}}

@extends('frontend.layouts.main')

@section('title', __('cookies.error_title'))

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
                        <h1 class="mb-2 text-danger">{{ __('cookies.error_title') }}</h1>
                        <p class="text-muted mb-4">
                            {{ __('cookies.error_message') }}
                        </p>

                        <a href="{{ route('cookies.settings') }}" class="btn btn-primary">
                            {{ __('cookies.back') }}
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
