<div class="interaction-row__left">
    @auth
        <form method="post" action="{{ route('content.like', [$type, $model->public_id]) }}" data-interaction-form>
            @csrf
            <button class="action-btn {{ $liked ? 'is-active' : '' }}" type="submit">
                <i class="bi bi-heart{{ $liked ? '-fill' : '' }}"></i>
                Нравится
            </button>
        </form>
        <form method="post" action="{{ route('content.bookmark', [$type, $model->public_id]) }}" data-interaction-form>
            @csrf
            <button class="action-btn {{ $saved ? 'is-active' : '' }}" type="submit">
                <i class="bi bi-bookmark{{ $saved ? '-fill' : '' }}"></i>
                {{ $saved ? 'Сохранено' : 'Сохранить' }}
            </button>
        </form>
    @else
        <a class="action-btn" href="{{ route('auth.login.form') }}">
            <i class="bi bi-heart"></i>Нравится
        </a>
        <a class="action-btn" href="{{ route('register.form') }}">
            <i class="bi bi-bookmark"></i>Сохранить
        </a>
    @endauth
</div>
<div class="interaction-row__right" data-like-count>♥ {{ $likeCount }}</div>

<div class="interaction-status visually-hidden" data-interaction-status role="status" aria-live="polite"></div>
