{{--
|--------------------------------------------------------------------------
| Account Deletion Cancelled View
|--------------------------------------------------------------------------
|
| This view is displayed after a user successfully cancels their account
| deletion request during the grace period.
|
| Route: GET /user/delete/cancelled → user.delete.cancelled
|
| This page confirms:
| - Deletion request has been cancelled
| - Account is fully restored
| - All data is preserved and accessible
| - User can continue using the service normally
|
| UI elements:
| - Success message with confirmation
| - "Go to Profile" button (link to user.account.index)
| - Reassuring tone that account is safe
|
| System actions performed:
| - User account restored (soft delete reversed)
| - Deletion schedule cleared
| - Confirmation email sent
| - All functionality immediately restored
|
| Email notifications:
| - "Deletion Cancelled" email sent to user
| - No further deletion reminder emails will be sent
|
| UX considerations:
| - Should feel like a "relief" or "welcome back" moment
| - Clear confirmation that no data was lost
| - Immediate return to full functionality
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.delete.cancelled.title'))

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
                        <h1 class="mb-2">{{ __('user.account.delete.cancelled.heading') }}</h1>
                        <p class="text-muted mb-3">{{ __('user.account.delete.cancelled.description') }}</p>

                        {{-- Статус --}}
                        <div class="alert alert-success mb-3">
                            <strong>{{ __('user.account.delete.cancelled.status') }}:</strong>
                            {{ __('user.account.delete.cancelled.restored') }}
                        </div>

                        <p class="mb-4">{{ __('user.account.delete.cancelled.message') }}</p>

                        {{-- Кнопка действия --}}
                        <a href="{{ route('user.account.index') }}" class="btn btn-primary px-4">
                            {{ __('user.account.delete.cancelled.profile_button') }}
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
