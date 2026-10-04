{{--
|--------------------------------------------------------------------------
| Registration — Email Sent / Confirmation
|--------------------------------------------------------------------------
|
| View: web.frontend.register.confirm
|
| Purpose:
|   Displayed after the user submits their email for registration.
|   Confirms that a verification link has been sent (or will be sent) to the
|   provided email without revealing whether the email exists in the system.
|
| Data passed to the view:
|   - Optional email (partially masked) for reference
|   - Optional flash messages (e.g., error, info)
|
| Typical content:
|   - Message: "Если вы ввели корректный email, проверьте вашу почту."
|   - Optional hint: "Если письмо не пришло, попробуйте повторно через несколько минут."
|   - Link/button: "Назад на форму регистрации" → GET /register
|
| Flow context:
|   → User submits email via POST /register
|   → RegisterController@send handles sending the tokenized email
|   → Render this view to confirm next steps
|
| Security/UX:
|   - Neutral message (prevents account enumeration)
|   - Friendly instructions
|   - Provides next steps clearly
|
| Route relation:
|   POST /register → RegisterController@send → render web.frontend.register.confirm
--}}

@extends('frontend.layouts.auth')

@section('title', __('register.confirm.title'))

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
                        <h1 class="mb-2">{{ __('register.confirm.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('register.confirm.subtitle') }}</p>

                        {{-- Сообщение --}}
                        <p class="mb-2">{{ __('register.confirm.message') }}</p>
                        <p class="text-muted">{{ __('register.confirm.hint') }}</p>

                        {{-- Действие --}}
                        <div class="mt-4">
                            <a href="{{ route('register.form') }}" class="btn btn-secondary">
                                {{ __('register.confirm.back_to_form') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
