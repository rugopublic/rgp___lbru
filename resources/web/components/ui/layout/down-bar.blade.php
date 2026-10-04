<div
    {{ $attributes->merge([
        'class' => 'ui-down-bar',
    ]) }}
    data-ui-down-bar
>
    <div class="ui-down-bar__inner">
        @isset($left)
            <div class="ui-down-bar__left">
                {{ $left }}
            </div>
        @endisset

        @isset($right)
            <div class="ui-down-bar__right">
                {{ $right }}
            </div>
        @endisset
    </div>
</div>
