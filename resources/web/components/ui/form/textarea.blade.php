@php
    $hasError = $name && $errors->has($name);
    $errorMessage = $name ? $errors->first($name) : null;

    $currentValue = old($name, $value);

    $sizeClass = match ($size) {
        'sm' => 'ui-form-textarea--sm',
        'lg' => 'ui-form-textarea--lg',
        default => 'ui-form-textarea--md',
    };
@endphp

<div
    {{ $attributes->merge([
        'class' => 'ui-form-field ui-form-textarea ' . $sizeClass,
    ]) }}
    data-ui-textarea
    data-min-rows="{{ $minRows }}"
    data-max-rows="{{ $maxRows }}"
    data-autosize="{{ $autosize ? 'true' : 'false' }}"
    data-ripple="{{ $ripple ? 'true' : 'false' }}"
>

    @if($label)
        <div class="ui-form-field__label-row">

            <label
                for="{{ $id }}"
                class="ui-form-field__label {{ $hasError ? 'is-error' : '' }}"
                data-textarea-label
            >
                <span>
                    {{ $label }}

                    @if($required)
                        <span class="ui-form-field__required">*</span>
                    @endif
                </span>

                @if($hasError)
                    <button
                        type="button"
                        class="ui-form-field__error-trigger"
                        data-textarea-error-trigger
                        aria-label="Показать ошибку"
                        aria-expanded="false"
                    >
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </button>
                @endif
            </label>

            @if($hasError)
                <div
                    class="ui-form-field__error-popover"
                    data-textarea-error
                    hidden
                >
                    {{ $errorMessage }}
                </div>
            @endif

        </div>
    @endif

    <div
        class="ui-form-textarea__control"
        data-textarea-control
    >

        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            class="ui-form-textarea__input {{ $hasError ? 'is-invalid' : '' }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($maxlength !== null) maxlength="{{ $maxlength }}" @endif
            @if($pattern) pattern="{{ $pattern }}" @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($autofocus) autofocus @endif
            @if($resize === 'none') data-resize="none" @else data-resize="{{ $resize }}" @endif
            data-textarea-input
            data-ripple-target
            rows="{{ $minRows }}"
            @if($pattern) data-pattern="{{ $pattern }}" @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if($hasError)
                aria-describedby="{{ $id }}-error"
            @endif
        >{{ $currentValue }}</textarea>

        @if($ripple)
            <span
                class="ui-form-textarea__ripple-layer"
                aria-hidden="true"
                data-textarea-ripple-layer
            ></span>
        @endif

    </div>

    @if($maxlength !== null && $meter)
        <div
            class="ui-form-textarea__meter field-meter"
            data-textarea-meter
        >
            <div class="field-meter-track">
                <div
                    class="field-meter-bar"
                    data-textarea-meter-bar
                ></div>
            </div>

            <span
                class="field-meter-label"
                data-textarea-meter-label
            >
                0 / {{ $maxlength }}
            </span>
        </div>
    @endif

    @if($hint)
        <div class="ui-form-field__hint editor-field-hint">
            {{ $hint }}
        </div>
    @endif

</div>
