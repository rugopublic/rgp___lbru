@unless($slot->isEmpty())
    <section {{ $attributes }}>
        <header>
            <h2 class="title">
                {{ $title }}
            </h2>
            @unless($subtitle === '')
                <p>
                    {{ $subtitle }}
                </p>
            @endunless
        </header>

        {{ $slot }}
    </section>
@endunless
