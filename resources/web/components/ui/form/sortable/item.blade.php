<div
    id="{{ $id }}"
    class="ui-form-sortable__item"
    data-sortable-item
    data-sortable-item-value="{{ $value }}"
    {{ $attributes->except([
            'id',
            'class',
            'data-sortable-item-value',
            'data-sortable-item-label-value',
        ]) }}
>
    <div class="ui-form-sortable__item-content">
        <span
            class="ui-form-sortable__item-handle"
            data-sortable-item-handle
            aria-hidden="true"
        >
            <i class="bi bi-grip-vertical"></i>
        </span>

        <div
            class="ui-form-sortable__item-label"
            data-sortable-item-label
            @if($attributes->has('data-sortable-item-label-value'))
                data-sortable-item-label-value="{{ $attributes->get('data-sortable-item-label-value') }}"
            @endif
        >
            {{ $slot }}
        </div>
    </div>

    <button
        type="button"
        class="btn btn-link"
        data-sortable-item-remove
        aria-label="Удалить элемент"
    >
        <i class="bi bi-trash"></i>
    </button>
</div>
