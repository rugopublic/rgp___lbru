{{--
|--------------------------------------------------------------------------
| Account Deletion Request / Confirmation View
|--------------------------------------------------------------------------
|
| This view is displayed when a user clicks "Delete Profile" from their
| account page. It explains the deletion process and provides two options:
| proceed with deletion or return to the profile.
|
| Route: GET /user/delete/request → user.delete.request
|
| Key information displayed:
| - Explanation of the two-step deletion process (soft delete + grace period)
| - List of what data will be permanently deleted
| - List of what data may be retained (for legal compliance)
| - Grace period duration (e.g., 30 days)
| - Instructions for canceling deletion during grace period
|
| UI elements:
| - RED "Delete My Profile" button (POST to user.delete.confirm)
| - SECONDARY "Keep My Profile" button (link to user.account.index)
| - Clear, non-alarming but serious tone
| - Optional: checkbox "I understand this action cannot be immediately undone"
|
| Security considerations:
| - CSRF token required for the deletion form
| - No pre-checked checkboxes
| - Clear language about consequences
|
| Legal requirements:
| - Must clearly explain consequences
| - Must not use deceptive patterns (dark patterns)
| - Must provide easy way to cancel/back out
|
--}}

@extends('frontend.layouts.auth')

@section('title', __('user.account.delete.request.title'))

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
                        <h1 class="mb-2">{{ __('user.account.delete.request.heading') }}</h1>
                        <p class="text-muted mb-3">{{ __('user.account.delete.request.description') }}</p>

                        {{-- Списки последствий --}}
                        <ul class="mb-3">
                            <li>{{ __('user.account.delete.request.permanent_data') }}</li>
                            <li>{{ __('user.account.delete.request.retained_data') }}</li>
                            <li>{{ __('user.account.delete.request.grace_period', ['days' => config('user.delete_grace_days', 30)]) }}</li>
                        </ul>

                        {{-- Форма подтверждения --}}
                        <form method="POST" action="{{ route('user.delete.confirm') }}">
                            @csrf

                            <div class="form-check mb-3">
                                <input class="form-check-input @error('acknowledge') is-invalid @enderror" type="checkbox" id="acknowledge" name="acknowledge" required>
                                <label class="form-check-label" for="acknowledge">
                                    {{ __('user.account.delete.request.acknowledge') }}
                                </label>
                                @error('acknowledge')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger px-4">
                                    {{ __('user.account.delete.request.submit') }}
                                </button>
                                <a href="{{ route('user.account.index') }}" class="btn btn-secondary px-4">
                                    {{ __('user.account.delete.request.keep') }}
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
