{{--
|--------------------------------------------------------------------------
| Account Deletion Confirmed View
|--------------------------------------------------------------------------
|
| This view is displayed immediately after a user confirms account deletion.
| The user's account has been soft-deleted (marked for deletion) and the
| grace period has begun.
|
| Route: GET /user/delete/confirmed → user.delete.confirmed
|
| This page confirms that:
| - The deletion request has been received and processed
| - Account is now in "scheduled for deletion" state
| - Grace period has started (e.g., "30 days until permanent deletion")
| - Confirmation email has been sent
|
| UI elements:
| - Clear success/confirmation message
| - "Log Out" button (logs user out immediately)
| - Information about what happens next
| - Note about canceling deletion (link to pending page after login)
|
| Important behaviors:
| - User is still logged in (must explicitly log out)
| - Page should be accessible only immediately after confirmation
| - Any subsequent login will redirect to pending page
|
| Email notifications:
| - "Deletion Request Received" email sent
| - "Final Warning" email will be sent 24-48h before permanent deletion
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.delete.confirmed.title'))

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
                        <h1 class="mb-2">{{ __('user.account.delete.confirmed.heading') }}</h1>
                        <p class="text-muted mb-3">{{ __('user.account.delete.confirmed.description') }}</p>

                        {{-- Списки информации --}}
                        <ul class="mb-4">
                            <li>{{ __('user.account.delete.confirmed.grace_period', ['days' => config('user.delete_grace_days', 30)]) }}</li>
                            <li>{{ __('user.account.delete.confirmed.next_steps') }}</li>
                            <li>{{ __('user.account.delete.confirmed.cancel_note') }}</li>
                        </ul>

                        {{-- Кнопка выхода --}}
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-warning px-4">
                                {{ __('user.account.delete.confirmed.logout') }}
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
