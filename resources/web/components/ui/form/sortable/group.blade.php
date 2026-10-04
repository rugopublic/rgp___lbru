<div
    id="{{ $id }}"
    class="ui-form-sortable__group"
    data-sortable-group
    data-sortable-group-id="{{ $groupId }}"
>
    <div class="ui-form-sortable__group-header">
        <span
            class="ui-form-sortable__group-handle"
            data-sortable-group-handle
            aria-hidden="true"
        >
            <i class="bi bi-grip-vertical"></i>
        </span>

        <span
            class="ui-form-sortable__group-title"
            data-sortable-group-title
        >
            {{ $label }}
        </span>

        <div class="ui-form-sortable__group-actions">
            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-edit
                aria-label="Изменить название группы"
            >
                <i class="bi bi-pencil"></i>
            </button>

            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-add-item
                aria-label="Добавить элемент"
            >
                <i class="bi bi-plus-lg"></i>
            </button>

            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-remove
                aria-label="Удалить группу"
            >
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <div
        class="ui-form-sortable__group-items"
        data-sortable-items
    >
        {{ $slot }}
    </div>
</div>
