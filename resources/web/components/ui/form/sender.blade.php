@once
    @vite(['resources/scripts/components/ui/form/sender.js'])
@endonce

<x-ui.layout.modal
    id="formSenderModal"
    labelled-by="formSenderModalLabel"
    class="form-sender-modal"
>
    <x-slot:header>
        <h2 class="modal-title fs-5" id="formSenderModalLabel">
            <span class="modal-title-text">Отправка формы</span>
        </h2>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Закрыть"
        ></button>
    </x-slot>

    <x-slot:body>
        <x-http.data-uploader />

        <div
            class="alert alert-danger d-none mt-3"
            data-sender-errors
            role="alert"
        ></div>
    </x-slot>

    <x-slot:footer>
        {{-- Кнопок пока нет --}}
    </x-slot>
</x-ui.layout.modal>
