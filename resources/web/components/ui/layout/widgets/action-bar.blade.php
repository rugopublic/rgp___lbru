@once
	@vite(['resources/styles/components/ui/layout/widgets/action-bar.scss'])
@endonce

<div
    @class([
        'action-bar',
        'sticky' => !$embedded
    ])
    {{ $attributes }}
>
    @isset($left)
        <div class="left">
            {{ $left }}
        </div>
    @endisset

    @isset($right)
        <div class="right">
            {{ $right }}
        </div>
    @endisset
</div>
