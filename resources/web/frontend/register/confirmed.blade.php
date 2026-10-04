{{--
|--------------------------------------------------------------------------
| Registration — Token Confirmed / Password Form
|--------------------------------------------------------------------------
|
| View: web.frontend.register.confirmed
|
| Purpose:
|   Displayed when the user follows a valid registration link.
|   Shows the form to input and confirm a password to finalize registration.
|
| Data passed to the view:
|   - Token string (hidden input)
|   - Optional validation errors: $errors
|
| Typical content:
|   - Input: password + password confirmation
|   - Button: "Завершить регистрацию" → PUT /register
|
| Flow context:
|   → User clicks tokenized email link
|   → RegisterController@verify validates token
|   → Token valid → render this view to set password
|
| Security/UX:
|   - Friendly instructions
|   - Clear CTA for finishing registration
|   - Does not reveal any sensitive info
|
| Route relation:
|   GET /register/{token} → RegisterController@verify → render web.frontend.register.confirmed
--}}

@extends('frontend.layouts.auth')

@section('title', __('register.confirmed.title'))

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
                        <h1 class="mb-2">{{ __('register.confirmed.title') }}</h1>
                        <p class="text-muted mb-4">{{ __('register.confirmed.subtitle') }}</p>

                        <form method="POST" action="{{ route('register.finalize') }}" novalidate>
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="token" value="{{ request()->route('token') }}">

                            {{-- Пароль --}}
                            <div class="mb-3">
                                <label for="p_input" class="form-label">{{ __('register.confirmed.password_label') }}</label>
                                <input
                                    type="password"
                                    id="p_input"
                                    name="p_input"
                                    required
                                    class="form-control @error('p_input') is-invalid @enderror"
                                    placeholder="Введите пароль"
                                    autocomplete="new-password"
                                    aria-invalid="{{ $errors->has('p_input') ? 'true' : 'false' }}"
                                >
                                @error('p_input')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Подтверждение пароля --}}
                            <div class="mb-3">
                                <label for="p_repeat" class="form-label">{{ __('register.confirmed.password_confirmation_label') }}</label>
                                <input
                                    type="password"
                                    id="p_repeat"
                                    name="p_repeat"
                                    required
                                    class="form-control"
                                    placeholder="Повторите пароль"
                                    autocomplete="new-password"
                                >
                            </div>

                            {{-- Кнопка --}}
                            <div class="d-flex justify-content-start gap-2 mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('register.confirmed.submit') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

