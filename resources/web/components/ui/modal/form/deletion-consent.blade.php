<x-ui.layout.modal
    id="deletionConsentModal"
    labelled-by="deletionConsentModalLabel"
    class="delete-modal"
>
    <x-slot:header>
        <h2 class="modal-title fs-5" id="deletionConsentModalLabel">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{-- A text such as "Delete Material", provided by JS from the specific form. --}}
            <span class="modal-title-text"></span>
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
            Вы собираетесь удалить
            {{--
                A text clarifying a specific section to manage,
                such as "Course", provided by JS from the specific form.
            --}}
            <span class="modal-material-type-label"></span>:
        </p>

        <div class="delete-target">
            {{--
                A text clarifying a title of a specific material,
                such as "My First Course", provided by JS from the specific form.
            --}}
            <span class="modal-material-title"></span>
        </div>

        <ul class="delete-consequences">
            {{--
                A list of consequences that deletion of this material will imply,
                provided by JS from the specific form.
            --}}
            <span class="modal-deletion-consequences"></span>
        </ul>

        <div class="delete-warning">
            <i class="bi bi-info-circle me-1"></i>
            После удаления <span class="modal-material-type-label"></span> нельзя будет восстановить из этой формы.
        </div>

        <div class="delete-confirmation">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="confirmDeleteCourse">
                <label class="form-check-label" for="confirmDeleteCourse">
                    Я понимаю последствия и хочу удалить <span class="modal-material-type-label"></span>.
                </label>
            </div>
        </div>
    </x-slot>

    <x-slot:footer>
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
            Отмена
        </button>

        {{--
            The submission of this form is handled by JS instead of 'action'.
            It does depend on the form that had opened this modal.
        --}}
        <form method="POST" class="m-0">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" disabled>
                <i class="bi bi-trash me-1"></i> Удалить <span class="modal-material-type-label"></span>
            </button>
        </form>
    </x-slot>
</x-ui.layout.modal>
