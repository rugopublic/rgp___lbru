{{--
|--------------------------------------------------------------------------
| Cookie Consent Updated Confirmation View
|--------------------------------------------------------------------------
|
| Confirms that the visitor's cookie consent settings have been updated.
| Shows the final state of consent checkboxes.
|
| Layout: web.frontend.layouts.main
| Localization: use __('cookies.*') keys
|
--}}

@extends('frontend.layouts.main')

@section('title', __('cookies.updated_title'))

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
                        <h1 class="mb-2">{{ __('cookies.updated_title') }}</h1>
                        <p class="text-muted mb-4">
                            {{ __('cookies.updated_message') }}
                        </p>

                        <ul class="list-unstyled">
                            <li>
                                <strong>{{ __('cookies.required') }}:</strong>
                                {{ __('cookies.enabled') }}
                            </li>
                            <li>
                                <strong>{{ __('cookies.functional') }}:</strong>
                                {{ $settings['functional'] ? __('cookies.enabled') : __('cookies.disabled') }}
                            </li>
                            <li>
                                <strong>{{ __('cookies.marketing') }}:</strong>
                                {{ $settings['marketing'] ? __('cookies.enabled') : __('cookies.disabled') }}
                            </li>
                        </ul>

                        <a href="{{ route('cookies.settings') }}" class="btn btn-primary mt-3">
                            {{ __('cookies.back') }}
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
