<header
    {{ $attributes->merge([
        'class' => 'ui-page-header',
    ]) }}
>
    @isset($breadcrumb)
        <div class="ui-page-header__breadcrumb">
            {{ $breadcrumb }}
        </div>
    @endisset

    <div class="ui-page-header__heading-row">
        <div class="ui-page-header__heading">
            @isset($title)
                <h1 class="ui-page-header__title">
                    {{ $title }}
                </h1>
            @endisset

            @isset($subtitle)
                <p class="ui-page-header__subtitle">
                    {{ $subtitle }}
                </p>
            @endisset
        </div>

        @isset($aside)
            <div class="ui-page-header__aside">
                {{ $aside }}
            </div>
        @endisset
    </div>
</header>
