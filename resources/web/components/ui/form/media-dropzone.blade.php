@php
    $inputId = 'media-dropzone-' . $name;
    $acceptAttribute = implode(',', $accept);
@endphp

<div
    {{ $attributes->merge([
        'class' => 'media-dropzone',
        'data-media-dropzone' => '',
        'data-name' => $name,
        'data-ratio' => $ratio,
        'data-image-max-size' => $imageMaxSize,
        'data-video-max-size' => $videoMaxSize,
        'data-image-width' => $imageWidth,
        'data-image-height' => $imageHeight,
        'data-image-quality' => $imageQuality,
        'data-existing-url' => $existingUrl,
    ]) }}
>
    {{-- File input --}}
    <input
        id="{{ $inputId }}"
        type="file"
        name="{{ $name }}"
        accept="{{ $acceptAttribute }}"
        class="media-dropzone__input visually-hidden"
        data-media-input
    >

    {{-- Dropzone surface --}}
    <div
        class="media-dropzone__surface"
        data-media-dropzone-area
    >
        {{-- Empty state --}}
        <label
            for="{{ $inputId }}"
            class="media-dropzone__empty"
        >
            <div class="media-dropzone__icon" aria-hidden="true">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>

            <div class="media-dropzone__content">
                <strong class="media-dropzone__title">
                    Выберите файл
                </strong>

                <span class="media-dropzone__description">
                    или перетащите его сюда
                </span>
            </div>
        </label>

        {{-- Selected / processed media --}}
        <div
            class="media-dropzone__preview"
            data-media-preview
            hidden
        >
            {{-- Image --}}
            <div
                class="media-dropzone__image-preview"
                data-media-image-preview
                hidden
            ></div>

            {{-- Video --}}
            <div
                class="media-dropzone__video-preview"
                data-media-video-preview
                hidden
            ></div>
        </div>

        {{-- File information (overlay at bottom) --}}
        <div
            class="media-dropzone__file-overlay"
            data-media-file
            hidden
        >
            <div class="media-dropzone__file-info">
                <i
                    class="bi bi-file-earmark"
                    aria-hidden="true"
                    data-media-file-icon
                ></i>

                <div class="media-dropzone__file-details">
                    <span
                        class="media-dropzone__file-name"
                        data-media-file-name
                    ></span>

                    <span
                        class="media-dropzone__file-size"
                        data-media-file-size
                    ></span>
                </div>
            </div>
        </div>

        {{-- Remove button --}}
        <button
            type="button"
            class="media-dropzone__remove"
            data-media-remove
            aria-label="Удалить выбранный файл"
            title="Удалить"
            hidden
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>

        {{-- Message overlay (ошибки / статусы) --}}
        <div
            class="media-dropzone__message"
            data-media-message
            role="status"
            aria-live="polite"
            hidden
        ></div>
    </div>

    {{-- Cropper container (невидимый, используется JS) --}}
    <div
        class="media-dropzone__cropper"
        data-media-cropper
        hidden
    ></div>

    {{-- Bootstrap modal --}}
    <x-ui.layout.modal
        id="media-dropzone-modal-{{ $name }}"
        labelled-by="media-dropzone-modal-label-{{ $name }}"
        class="media-dropzone__cropper-modal modal-xl"
        data-media-cropper-modal
    >
        <x-slot:header>
            <div>
                <h1
                    class="modal-title fs-5"
                    id="media-dropzone-modal-label-{{ $name }}"
                >
                    Кадрирование изображения
                </h1>

                <p class="media-dropzone__cropper-subtitle mb-0">
                    Настройте область изображения и проверьте результат
                    перед сохранением.
                </p>
            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Закрыть"
            ></button>
        </x-slot>

        <x-slot:body>
            <div class="media-dropzone__cropper-layout">

                {{-- Cropper workspace --}}
                <section
                    class="media-dropzone__cropper-workspace"
                    aria-label="Область кадрирования"
                >
                    <div class="media-dropzone__cropper-workspace-header">
                        <div>
                            <h2 class="media-dropzone__cropper-panel-title">
                                Кадрирование
                            </h2>

                            <p class="media-dropzone__cropper-panel-description">
                                Перемещайте изображение и изменяйте область
                                кадрирования.
                            </p>
                        </div>
                    </div>

                    <div
                        class="media-dropzone__cropper-container"
                        data-media-cropper-container
                    ></div>
                </section>

                {{-- Preview panel --}}
                <aside
                    class="media-dropzone__cropper-preview-panel"
                    aria-label="Предпросмотр результата"
                >
                    <div class="media-dropzone__cropper-preview-header">
                        <div>
                            <h2 class="media-dropzone__cropper-panel-title">
                                Предпросмотр
                            </h2>

                            <p class="media-dropzone__cropper-panel-description">
                                Так будет выглядеть итоговое изображение.
                            </p>
                        </div>
                    </div>

                    <div class="media-dropzone__cropper-preview-stage">
                        <div
                            class="media-dropzone__cropper-preview-frame"
                            data-media-cropper-preview
                        >
                            <div
                                class="media-dropzone__cropper-preview-placeholder"
                                data-media-cropper-preview-placeholder
                            >
                                <i
                                    class="bi bi-image"
                                    aria-hidden="true"
                                ></i>

                                <span>
                                    Предпросмотр
                                </span>
                            </div>

                            <canvas
                                class="media-dropzone__cropper-preview-canvas"
                                data-media-cropper-preview-canvas
                                hidden
                            ></canvas>
                        </div>
                    </div>
                </aside>

            </div>
        </x-slot>

        <x-slot:footer>
            <div class="d-flex gap-2 me-auto">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-media-cropper-reset
                >
                    <i
                        class="bi bi-arrow-counterclockwise"
                        aria-hidden="true"
                    ></i>
                    Сбросить
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-media-cropper-fit-width
                >
                    <i
                        class="bi bi-arrows-expand-horizontal"
                        aria-hidden="true"
                    ></i>
                    По ширине
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-media-cropper-fit-height
                >
                    <i
                        class="bi bi-arrows-expand-vertical"
                        aria-hidden="true"
                    ></i>
                    По высоте
                </button>
            </div>

            <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
                data-media-cropper-cancel
            >
                Отмена
            </button>

            <button
                type="button"
                class="btn btn-primary"
                data-media-cropper-apply
            >
                <i class="bi bi-check-lg" aria-hidden="true"></i>
                Применить
            </button>
        </x-slot>
    </x-ui.layout.modal>
</div>
