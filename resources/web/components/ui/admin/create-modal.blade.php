@props([
    'id',
    'title',
    'action',
    'description' => 'Заполните только то, без чего запись нельзя создать. Остальное спокойно настроите на странице редактирования.',
    'submitLabel' => 'Создать и открыть редактор',
])

<div class="modal fade admin-create-modal" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" data-admin-create-modal>
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST" data-admin-create-form>
                @csrf
                <div class="modal-header">
                    <div>
                        <div class="admin-eyebrow">Новое</div>
                        <h2 class="modal-title" id="{{ $id }}Label">{{ $title }}</h2>
                        <p class="admin-modal-description">{{ $description }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-create-errors" data-admin-create-errors hidden></div>
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Пока не создавать</button>
                    <button type="submit" class="btn btn-primary" data-admin-create-submit>
                        <span data-label>{{ $submitLabel }}</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" data-spinner aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
