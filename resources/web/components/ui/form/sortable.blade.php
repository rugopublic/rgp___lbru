@once
    @vite([
        'resources/styles/components/ui/form/sortable.scss',
        'resources/scripts/components/ui/form/sortable.js',
    ])
@endonce

<div
    class="ui-form-sortable"
    data-sortable
    data-sortable-endpoint="{{ $endpoint }}"
    data-sortable-name="{{ $name }}"
>
    @if($label)
        <label
            for="{{ $id }}"
            class="form-label"
        >
            {{ $label }}
        </label>
    @endif

    @if($button)
        <button
            type="button"
            class="btn btn-outline-primary btn-sm mt-2"
            data-sortable-group-add
        >
            <i class="bi bi-plus-lg"></i>
            <span>Добавить группу</span>
        </button>
    @endif

    {{-- Groups --}}
    <div
        class="ui-form-sortable__groups"
        data-sortable-groups
    >
        {{ $slot }}
    </div>

    {{-- Items вне любой группы --}}
    @unless($grouped)
        <div
            class="ui-form-sortable__items mt-5"
            data-sortable-root-items
        >
            {{ $rootItems ?? '' }}
        </div>
    @endunless

    <input
        type="hidden"
        id="{{ $id }}"
        name="{{ $name }}"
        data-sortable-value
        value="{{ json_encode($value ?? []) }}"
    >
</div>
