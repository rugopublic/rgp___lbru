<div class="comment-list">
    @forelse($comments as $comment)
        <div class="comment">
            <div class="comment__avatar">
                {{ mb_strtoupper(mb_substr($comment->user->account->name ?? $comment->user->email, 0, 1)) }}
            </div>
            <div>
                <strong>{{ $comment->user->account->name ?? 'Пользователь' }}</strong>
                <p>{{ $comment->body }}</p>
                <small>{{ $comment->created_at->diffForHumans() }}</small>
            </div>
        </div>
    @empty
        <p class="muted">Пока никто не написал. Будьте первым.</p>
    @endforelse
</div>

@auth
    <form class="comment-form" method="post" action="{{ route('content.comment', [$type, $model->public_id]) }}" data-comment-form>
        @csrf
        <textarea name="body" rows="4" class="form-control" placeholder="Поделитесь впечатлением или задайте вопрос…" required></textarea>
        <button class="btn btn-brand mt-2">Опубликовать комментарий</button>
        <div class="interaction-status visually-hidden" data-comment-status role="status" aria-live="polite"></div>
    </form>
@else
    <div class="comment-login">
        <p>Комментарии открыты зарегистрированным читателям.</p>
        <a href="{{ route('register.form') }}" class="btn btn-quiet">Создать профиль</a>
    </div>
@endauth
