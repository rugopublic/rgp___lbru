@props([
    'name',
    'label' => null,
    'value' => null,
    'id' => null,
    'type' => 'text',

    'placeholder' => null,

    'size' => null,

    'maxlength' => null,
    'minlength' => null,

    'pattern' => null,

    'autocomplete' => null,

    'required' => false,
    'autofocus' => false,
    'disabled' => false,
    'readonly' => false,

    'hint' => null,

    'meter' => false,

    'meterId' => null,
    'meterLabelId' => null,

    'error' => null,
    'errorId' => null,

    'advisor' => false,
    'advisorId' => null,
])

@php
    $inputId = $id ?: $name;
    $meterId = $meterId ?: $inputId . '-meter';
    $meterLabelId = $meterLabelId ?: $inputId . '-meter-label';
    $errorId = $errorId ?: $inputId . '-error';
    $advisorId = $advisorId ?: $inputId . '-advisor';

    $hasError = $errors->has($name);
    $errorMessage = $error ?: $errors->first($name);

    $inputClasses = [
        'form-control',
        'ui-input__control',
        $size ? 'form-control-' . $size : null,
        $hasError ? 'is-invalid' : null,
    ];

    $currentValue = old($name, $value);
@endphp

<div
    {{ $attributes->merge([
        'class' => 'ui-input',
    ]) }}
    data-ui-input
    @if($pattern)
        data-ui-input-pattern="{{ $pattern }}"
    @endif
    @if($maxlength)
        data-ui-input-maxlength="{{ $maxlength }}"
    @endif
>
    @if($label)
        <div class="ui-input__label-row">

            <label
                for="{{ $inputId }}"
                class="form-label ui-input__label {{ $hasError ? 'is-error' : '' }}"
                data-ui-input-label
            >
                <span>
                    {{ $label }}

                    @if($required)
                        <span class="required-mark">*</span>
                    @endif
                </span>

                @if($hasError)
                    <button
                        type="button"
                        class="ui-input__error-toggle"
                        data-ui-input-error-toggle
                        aria-label="Показать ошибку"
                        aria-expanded="false"
                        aria-controls="{{ $errorId }}"
                    >
                        <i class="bi bi-exclamation-circle"></i>
                    </button>
                @endif
            </label>

        </div>
    @endif

    <div class="ui-input__control-wrap" data-ui-input-wrap>

        <input
            {{ $attributes->except(['class']) }}
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $currentValue }}"
            @class($inputClasses)
            @if($placeholder)
                placeholder="{{ $placeholder }}"
            @endif
            @if($maxlength)
                maxlength="{{ $maxlength }}"
            @endif
            @if($minlength)
                minlength="{{ $minlength }}"
            @endif
            @if($pattern)
                pattern="{{ $pattern }}"
            @endif
            @if($autocomplete)
                autocomplete="{{ $autocomplete }}"
            @endif
            @required($required)
            @autofocus($autofocus)
            @disabled($disabled)
            @readonly($readonly)
            @if($hasError)
                aria-invalid="true"
                aria-describedby="{{ $errorId }}"
            @endif
            data-ui-input-control
        >

    </div>

    @if($meter && $maxlength)
        <div
            class="field-meter ui-input__meter"
            data-ui-input-meter
            id="{{ $meterId }}"
        >
            <div class="field-meter-track">
                <div
                    class="field-meter-bar"
                    data-ui-input-meter-bar
                ></div>
            </div>

            <span
                class="field-meter-label"
                id="{{ $meterLabelId }}"
                data-ui-input-meter-label
            >
                {{ mb_strlen($currentValue ?? '') }} / {{ $maxlength }}
            </span>
        </div>
    @endif

    @if($hint)
        <div class="editor-field-hint ui-input__hint">
            {{ $hint }}
        </div>
    @endif

    @if($advisor)
        <div
            class="smart-advisor ui-input__advisor"
            id="{{ $advisorId }}"
            data-ui-input-advisor
        >
            <i class="bi"></i>
            <span></span>
        </div>
    @endif

    @if($hasError)
        <div
            class="ui-input__error"
            id="{{ $errorId }}"
            data-ui-input-error
            role="alert"
            aria-hidden="true"
        >
            {{ $errorMessage }}
        </div>
    @endif
</div>
