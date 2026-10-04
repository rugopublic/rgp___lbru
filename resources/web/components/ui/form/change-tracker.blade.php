@once
    @vite([
        'resources/styles/components/ui/form/change-tracker.scss',
        'resources/scripts/components/ui/form/change-tracker.js'
    ])
@endonce

<div class="change-tracker">
    <button
        type="button"
        class="state"
        data-bs-toggle="modal"
        data-bs-target="#formChangeLogModal"
    >
        <span class="indicator"></span>
        <span class="label">Изменений: <span class="count">0</span></span>
    </button>
</div>
