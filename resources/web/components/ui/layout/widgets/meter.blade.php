<div class="meter">
    <div
        class="track"
        id="{{ $id }}"
        role="meter"
        aria-valuemin="{{ $min }}"
        aria-valuemax="{{ $max }}"
        aria-valuenow="{{ $value }}"
        aria-labelledby="{{ $id }}-label"
    >
        <div class="bar" style="width: {{ $percentageValue() }}%"></div>
    </div>

    <span id="{{ $id }}-label">
        @if ($relative)
            {{ $percentageValue() }}%
        @else
            {{ $value }} / {{ $max }}
        @endif
    </span>
</div>
