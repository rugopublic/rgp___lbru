{{--
|--------------------------------------------------------------------------
| User Account Welcome / Setup View
|--------------------------------------------------------------------------
|
| This view is displayed to newly registered users who have verified their
| email but have not yet created their user account (profile).
|
| The route is protected by the "auth" and "verified" middleware and
| is accessible via the route name "user.account.welcome".
|
| Key routes involved:
| - GET  /user/account/welcome ............. → user.account.welcome
| - POST /user/account/setup (planned) ..... → user.account.setup
|
| This view is typically shown when:
| - User completes email verification but has no account record
| - User visits /user/account without an account (redirected by middleware)
| - User needs to complete initial profile setup
|
| This view should contain:
| - Welcome message and instructions
| - Form for initial profile setup (name, avatar, etc.)
| - Clear call-to-action to create the account
| - Optional skip-for-now link (if partial setup is allowed)
|
| Data passed to this view:
| - $user ............. Authenticated user model (without account)
|
| Business logic flow:
| 1. User registers → receives verification email
| 2. User verifies email → redirected to this welcome page
| 3. User fills profile form → account record created
| 4. User redirected to main account page (/user/account)
|
| Related Middleware:
| - EnsureUserAccountExists (planned) - redirects here if account missing
|
| Security considerations:
| - User must be authenticated and email verified
| - CSRF protection required for any forms
| - Rate limiting may be applied to setup requests
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.welcome.title'))

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
                            <h1 class="mb-2">{{ __('user.account.welcome.heading') }}</h1>
                            <p class="text-muted">{{ __('user.account.welcome.description') }}</p>
                        </header>

                        <form method="POST" action="{{ route('user.account.store') }}" class="auth__form">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('user.account.welcome.name') }}</label>
                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    class="form-control @error('name') is-invalid @enderror"
                                >
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-start mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('user.account.welcome.submit') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
