<div>
    @isset($label)
        <label
            for="{{ $id }}"
            class="form-label"
        >
            <span>
                {{ $label }}

                @if($required)
                    <span class="required-mark">*</span>
                @endif
            </span>
        </label>
    @endisset

    <select
        {{ $attributes->class(['form-select']) }}
        id="{{ $id }}"
        name="{{ $name }}"
        @required($required)
    >
        <option value="" @selected($value === null) @disabled($required)>
            {{ $fallback }}
        </option>

        @foreach($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                @selected((string)$value == (string)$optionValue)
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @isset($hint)
        <div class="hint">
            {{ $hint }}
        </div>
    @endisset
</div>
