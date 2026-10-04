{{--
|--------------------------------------------------------------------------
| User Account Creation Success View
|--------------------------------------------------------------------------
|
| This view is displayed immediately after a user successfully creates
| their account (profile) for the first time.
|
| The route is protected by the "auth" and "verified" middleware and
| is accessible via the route name "user.account.created".
|
| IMPORTANT: This page is intentionally ephemeral and guarded:
| - Only accessible via redirect from the store() method
| - Requires the 'from_welcome' session flag
| - Automatically redirects to welcome page if accessed directly
| - Session flag is cleared after first view (one-time access)
|
| Key routes involved:
| - GET  /user/account/welcome ..... → user.account.welcome (form)
| - POST /user/account ............ → user.account.store (processing)
| - GET  /user/account/created .... → user.account.created (this success page)
| - GET  /user/account ........... → user.account.index (main profile)
|
| Business logic flow:
| 1. User without account visits /user/account → redirected to welcome
| 2. User fills form on welcome page → POST to store()
| 3. Account created → redirect to this success page with 'from_welcome' flag
| 4. This page shows congratulations → user proceeds to main profile
| 5. Any subsequent direct access → redirects back to welcome
|
| This view should contain:
| - Celebratory message and confirmation of account creation
| - Summary of created profile (name, etc.)
| - Clear call-to-action to proceed to main profile page
| - Optional "Edit profile" button for immediate adjustments
| - Welcome message and next steps guidance
|
| Data passed to this view:
| - $user ............. Authenticated user model
| - $account .......... Newly created UserAccount model (via $user->account)
|
| Security considerations:
| - Session flag 'from_welcome' must be present
| - User must have a verified email
| - User must have exactly one account (recently created)
| - Flag is cleared after rendering to prevent replay
|
| UX considerations:
| - Page should feel like a celebration/achievement
| - Clear progression to next step (main profile)
| - Option to edit immediately if something was entered incorrectly
| - May include onboarding tips or next features to explore
|
--}}

@extends('frontend.layouts.main')

@section('title', __('user.account.created.title'))

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
                            <h1 class="mb-2">{{ __('user.account.created.heading') }}</h1>
                            <p class="text-muted">
                                {{ __('user.account.created.description', ['name' => $user->account->name]) }}
                            </p>
                        </header>

                        <div class="d-flex flex-column flex-md-row gap-2 mt-3">
                            <a href="{{ route('user.account.index') }}" class="btn btn-primary px-4">
                                {{ __('user.account.created.go_to_profile') }}
                            </a>

                            <a href="{{ route('user.account.edit') }}" class="btn btn-secondary px-4">
                                {{ __('user.account.created.edit_profile') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
