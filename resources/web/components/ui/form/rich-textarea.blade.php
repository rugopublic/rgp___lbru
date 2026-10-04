@once
    @vite([
        'resources/scripts/components/ui/form/rich-textarea.js',
        'resources/styles/components/ui/form/rich-textarea.scss'
    ])
@endonce

<div class="rich-editor">

    <div class="editor-toolbar">

        {{-- Text formatting --}}
        <button
            type="button"
            data-command="bold"
            title="Жирный"
        >
            <i class="bi bi-type-bold"></i>
        </button>

        <button
            type="button"
            data-command="italic"
            title="Курсив"
        >
            <i class="bi bi-type-italic"></i>
        </button>

        <button
            type="button"
            data-command="underline"
            title="Подчёркнутый"
        >
            <i class="bi bi-type-underline"></i>
        </button>

        <button
            type="button"
            data-command="strikeThrough"
            title="Зачёркнутый"
        >
            <i class="bi bi-type-strikethrough"></i>
        </button>


        {{-- Block formatting --}}
        <span class="border-start mx-2"></span>

        <button
            type="button"
            data-command="formatBlock"
            data-value="h2"
            title="Заголовок H2"
        >
            <i class="bi bi-type-h2"></i>
        </button>

        <button
            type="button"
            data-command="formatBlock"
            data-value="h3"
            title="Заголовок H3"
        >
            <i class="bi bi-type-h3"></i>
        </button>

        <button
            type="button"
            data-command="formatBlock"
            data-value="p"
            title="Параграф"
        >
            <i class="bi bi-paragraph"></i>
        </button>

        <span class="border-start mx-2"></span>

        <button
            type="button"
            data-command="insertImage"
            title="Изображение"
            aria-label="Вставить изображение"
        >
            <i class="bi bi-image"></i>
        </button>

        <span class="border-start mx-2"></span>

        <div class="dropdown rta-alert-dropdown">
            <button
                type="button"
                class="dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Вставить уведомление"
                aria-label="Вставить уведомление"
            >
                <i class="bi bi-info-square"></i>
            </button>

            <ul class="dropdown-menu">
                <li>
                    <button
                        type="button"
                        class="dropdown-item"
                        data-rta-alert-type="info"
                    >
                        <i class="bi bi-info-circle text-info"></i>
                        <span>Информация</span>
                    </button>
                </li>

                <li>
                    <button
                        type="button"
                        class="dropdown-item"
                        data-rta-alert-type="success"
                    >
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Успех</span>
                    </button>
                </li>

                <li>
                    <button
                        type="button"
                        class="dropdown-item"
                        data-rta-alert-type="warning"
                    >
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        <span>Внимание</span>
                    </button>
                </li>

                <li>
                    <button
                        type="button"
                        class="dropdown-item"
                        data-rta-alert-type="danger"
                    >
                        <i class="bi bi-exclamation-octagon text-danger"></i>
                        <span>Важно</span>
                    </button>
                </li>
            </ul>
        </div>


        {{-- Lists --}}
        <span class="border-start mx-2"></span>

        <button
            type="button"
            data-command="insertUnorderedList"
            title="Маркированный список"
        >
            <i class="bi bi-list-ul"></i>
        </button>

        <button
            type="button"
            data-command="insertOrderedList"
            title="Нумерованный список"
        >
            <i class="bi bi-list-ol"></i>
        </button>


        {{-- Alignment --}}
        <span class="border-start mx-2"></span>

        <button
            type="button"
            data-command="justifyLeft"
            title="По левому краю"
        >
            <i class="bi bi-text-left"></i>
        </button>

        <button
            type="button"
            data-command="justifyCenter"
            title="По центру"
        >
            <i class="bi bi-text-center"></i>
        </button>

        <button
            type="button"
            data-command="justifyRight"
            title="По правому краю"
        >
            <i class="bi bi-text-right"></i>
        </button>


        {{-- Links --}}
        <span class="border-start mx-2"></span>

        <button
            type="button"
            data-command="createLink"
            title="Ссылка"
        >
            <i class="bi bi-link-45deg"></i>
        </button>

    </div>


    {{-- Visual editor --}}
    <div
        id="{{ $id }}"
        contenteditable="true"
        class="editor-content"
        role="textbox"
        aria-multiline="true"
    >{!! $value !!}</div>


    {{-- Real form field --}}
    <textarea
        name="{{ $name }}"
        id="{{ $inputId }}"
        class="d-none"
        data-rich-textarea
        aria-labelledby="{{ $labelledby ?? '' }}"
    >{{ $value }}</textarea>

</div>
