<x-ui.layout.modal
    id="formChangeLogModal"
    labelled-by="formChangeLogModalLabel"
    class="modal"
>
    <x-slot:header>
        <h2 class="modal-title fs-5" id="formChangeLogModalLabel">
            Список изменений формы
        </h2>
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Закрыть"
        >
        </button>
    </x-slot>

    <x-slot:body>
        <p class="mb-2">
            С момента получения формы внесены следующие изменения:
        </p>

        {{-- Whatever gets changed in the form will be added to this block. --}}
        <div data-change-list></div>
    </x-slot>
</x-ui.layout.modal>
