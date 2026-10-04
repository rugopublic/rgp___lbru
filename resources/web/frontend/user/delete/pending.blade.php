{{--
|--------------------------------------------------------------------------
| Account Deletion Pending / Grace Period View
|--------------------------------------------------------------------------
|
| This view is displayed to users who have requested deletion but are still
| within the grace period. Any authenticated request from a soft-deleted
| user should redirect here (via middleware).
|
| Route: GET /user/delete/pending → user.delete.pending
|
| This page shows:
| - Current status: "Account scheduled for deletion"
| - Time remaining until permanent deletion (countdown/days)
| - List of what will happen when grace period expires
| - Option to cancel deletion
|
| UI elements:
| - Countdown timer or "X days remaining" display
| - "Cancel Deletion" button (POST to user.delete.cancel)
| - "Log Out" button
| - Clear visual indicator of account status (warning/yellow theme)
|
| Data displayed:
| - $daysRemaining ........ Days until permanent deletion
| - $deletionDate ......... Scheduled permanent deletion date
| - $user ................. Soft-deleted user model
|
| Security considerations:
| - Only accessible to soft-deleted users
| - CSRF protection for cancel action
| - Automatic logout after permanent deletion
|
| Integration:
| - Connected to console command that sends reminder emails
| - Connected to final deletion command
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.delete.pending.title'))

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
                        <h1 class="mb-2">{{ __('user.account.delete.pending.heading') }}</h1>
                        <p class="text-muted mb-3">{{ __('user.account.delete.pending.description') }}</p>

                        {{-- Статус --}}
                        <div class="alert alert-warning mb-3">
                            <strong>{{ __('user.account.delete.pending.status') }}:</strong>
                            {{ __('user.account.delete.pending.scheduled') }}
                        </div>

                        {{-- Информация о грейс-периоде --}}
                        <ul class="mb-4">
                            <li>{{ __('user.account.delete.pending.days_remaining', ['days' => $days_remaining]) }}</li>
                            <li>{{ __('user.account.delete.pending.final_date', ['date' => $final_deletion_date->format('d.m.Y')]) }}</li>
                        </ul>

                        {{-- Кнопки действий --}}
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('user.delete.cancel') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary px-4">
                                    {{ __('user.account.delete.pending.cancel_button') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-warning px-4">
                                    {{ __('user.account.delete.pending.logout_button') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
