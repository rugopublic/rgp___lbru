<div
    {{ $attributes->merge([
        'class' => 'ui-modal modal fade',
    ]) }}
    tabindex="-1"
    aria-labelledby="{{ $labelledBy }}"
    aria-hidden="true"
>
    <div @class([
        'modal-dialog',
        'modal-dialog-centered',
        'modal-' . $size => $size,
    ])>
        <div class="modal-content">

            @isset($header)
                <div class="modal-header">
                    {{ $header }}
                </div>
            @endisset

            @isset($body)
                <div class="modal-body">
                    {{ $body }}
                </div>
            @endisset

            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset

        </div>
    </div>
</div>
