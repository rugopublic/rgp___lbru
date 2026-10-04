@once
    @vite([
        'resources/styles/components/ui/form/typeahead.scss',
        'resources/scripts/components/ui/form/typeahead.js',
    ])
@endonce

<div
    class="ui-form-typeahead"
    data-typeahead
    data-typeahead-endpoint="{{ $endpoint }}"
>
    @if($label)
        <div class="ui-input__label-row">

            <label
                for="{{ $id }}"
                class="form-label ui-input__label"
                data-ui-input-label
            >
                <span>
                    {{ $label }}

                    @if($required)
                        <span class="required-mark">*</span>
                    @endif
                </span>
            </label>

        </div>
    @endif

    <div class="ui-form-typeahead__control">
        <input
            type="text"
            class="form-control"
            autocomplete="off"
            data-typeahead-input
        >

        <div
            class="ui-form-typeahead__selected"
            data-typeahead-selected
        >
            <span data-typeahead-selected-label></span>

            <button
                type="button"
                class="ui-form-typeahead__clear"
                data-typeahead-clear
                aria-label="Удалить выбранное значение"
            >
                &times;
            </button>
        </div>

        <div
            class="ui-form-typeahead__results"
            data-typeahead-results
            hidden
        >
            <div
                class="ui-form-typeahead__loading"
                data-typeahead-loading
                hidden
            >
                Загрузка...
            </div>

            <div
                class="ui-form-typeahead__options"
                data-typeahead-options
            ></div>

            <div
                class="ui-form-typeahead__empty"
                data-typeahead-empty
                hidden
            >
                Ничего не найдено.
            </div>
        </div>
    </div>

    <input
        type="hidden"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        data-typeahead-value
    >

    <script type="application/json" data-typeahead-initial-options>
        @json($options)
    </script>
</div>
