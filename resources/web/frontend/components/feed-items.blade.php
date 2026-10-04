@foreach($feed as $item)
    <article class="feed-post">
        <div class="feed-post__head"><span class="eyebrow"><i class="bi bi-dot"></i> {{ $item['badge'] }} @if($item['category']) · {{ $item['category'] }} @endif</span><time>{{ optional($item['published_at'])->translatedFormat('j F Y') }}</time></div>
        @if($item['image'])
            <a href="{{ $item['url'] }}" class="feed-post__media"><img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy"></a>
        @else
            <div class="feed-post__media feed-post__media--empty"><i class="bi bi-stars"></i></div>
        @endif
        <div class="feed-post__body">
            <h3><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></h3>
            @if($item['excerpt'])<p>{{ \Illuminate\Support\Str::limit(strip_tags($item['excerpt']), 330) }}</p>@endif
            <div class="feed-post__actions">
                @auth
                    @php($liked=(bool) ($item['liked'] ?? false))
                    @php($saved=(bool) ($item['saved'] ?? false))
                    <form method="POST" action="{{ route('content.like',['type'=>$item['type'],'id'=>$item['model']->public_id]) }}" data-interaction-form>@csrf<button class="action-btn {{ $liked ? 'is-active' : '' }}" type="submit"><i class="bi {{ $liked?'bi-heart-fill':'bi-heart' }}"></i><span>{{ $item['model']->likes_count ?? 0 }}</span></button></form>
                    <form method="POST" action="{{ route('content.bookmark',['type'=>$item['type'],'id'=>$item['model']->public_id]) }}" data-interaction-form>@csrf<button class="action-btn {{ $saved ? 'is-active' : '' }}" type="submit"><i class="bi {{ $saved?'bi-bookmark-fill':'bi-bookmark' }}"></i><span>{{ $item['model']->bookmarks_count ?? 0 }}</span></button></form>
                    <a class="action-btn" href="{{ $item['url'] }}#comments"><i class="bi bi-chat-heart"></i><span>{{ $item['model']->comments_count ?? 0 }}</span></a>
                @else
                    <a class="action-btn" href="{{ route('register.form') }}"><i class="bi bi-heart"></i> Нравится</a>
                    <a class="action-btn" href="{{ route('register.form') }}"><i class="bi bi-bookmark"></i> Сохранить</a>
                    <a class="action-btn" href="{{ $item['url'] }}#comments"><i class="bi bi-chat-heart"></i> Обсудить</a>
                @endauth
                <a class="action-btn action-btn--read" href="{{ $item['url'] }}">Открыть <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </div>
    </article>
@endforeach
