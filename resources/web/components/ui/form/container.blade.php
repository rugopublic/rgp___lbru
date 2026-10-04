@once
	@vite(['resources/styles/components/ui/form/container.scss'])
@endonce

<form
    data-track-id="{{ $identifier }}"
    method="{{ $formMethodAttribute() }}"
    action="{{ $endpoint }}"
    {{ $attributes->merge([
        "data-ajax-form" => ( $ajax ? "true" : null )
    ]) }}
    class="ui-form-container"
>
    {{-- Spoof method when necessary. --}}
    @if($requiresMethodField())
        @method($method->value)
    @endif

    @csrf

    @isset($main)
        <x-ui.form.container.main>
            {{ $main }}
        </x-ui.form.container.main>
    @endisset

    @isset($aside)
        <x-ui.form.container.aside>
            {{ $aside }}
        </x-ui.form.container.aside>
    @endisset

    <x-ui.layout.widgets.action-bar
        :embedded="$embedded"
    >
        <x-slot:left>
            @if($removable)
                {{-- Delete Action (if set) --}}
                <button
                    type="button"
                    class="btn btn-outline-danger editor-delete-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#deletionConsentModal"
                >
                    <i class="bi bi-trash me-1"></i> Удалить
                </button>
            @endif
            {{-- Other Actions --}}
            {{ $actions ?? '' }}
        </x-slot>

        <x-slot:right>
            {{-- Changed Fields Tracker (only they will be sent) --}}
            <x-ui.form.change-tracker />
            {{-- Reset Button --}}
            <button type="reset" class="btn btn-light border">
                {{ $resetButtonLabel() }}
            </button>
            {{-- Submit Button --}}
            <button
                type="submit"
                class="btn btn-primary editor-save-btn"
            >
                <i class="bi bi-check2 me-1"></i> {{ $submitButtonLabel() }}
            </button>
        </x-slot>
    </x-ui.layout.widgets.action-bar>

    @if($removable)
        {{-- The consequences of deleting the underlying object related to the form. --}}
        <div class="d-none" data-deletion-consequences>
            @isset($deletionConsequences)
                @if(!$deletionConsequences->hasActualContent())
                    @if(trim($deletionConsequences) === '')
                        <p>Удаление не повлечёт никаких побочных последствий.</p>
                    @endif
                @endif
                {{ $deletionConsequences }}
            @endisset
        </div>
    @endif
</form>
