{{-- Форма с прогресс-модалом: подключается глобально в layouts/admin --}}
<div class="modal fade" id="formProgressModal" tabindex="-1" aria-labelledby="formProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formProgressModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i>Обработка формы
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted mb-3" id="formProgressStatus">Подготовка...</p>

                <div class="progress mb-4" style="height: 6px;">
                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        id="formProgressBar"
                        role="progressbar"
                        style="width: 0%;"
                        aria-valuenow="0"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>
                </div>

                <div id="formProgressErrors" class="alert alert-danger d-none" role="alert"></div>
                <ul class="list-unstyled mb-0" id="formProgressSteps"></ul>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary d-none" id="formProgressCloseBtn" data-bs-dismiss="modal">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('formProgressModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;

    const progressModal = new bootstrap.Modal(modalElement);
    const progressBar = document.getElementById('formProgressBar');
    const progressStatus = document.getElementById('formProgressStatus');
    const progressSteps = document.getElementById('formProgressSteps');
    const progressErrors = document.getElementById('formProgressErrors');
    const closeBtn = document.getElementById('formProgressCloseBtn');

    // Файлы больше 5 МБ грузим чанками.
    const CHUNK_SIZE = 5 * 1024 * 1024;
    const CHUNK_THRESHOLD = 5 * 1024 * 1024;

    function setProgress(percent) {
        percent = Math.min(100, Math.max(0, percent));
        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', percent);
    }

    function getTempFieldName(originalName) {
        const match = originalName.match(/^(.*)\[([^\]]+)\]$/);
        if (match) {
            return `${match[1]}[${match[2]}_temp_id]`;
        }
        return originalName + '_temp_id';
    }

    function hasTempField(form, fileInput) {
        const tempName = getTempFieldName(fileInput.name);
        return Array.from(form.querySelectorAll('input[type="hidden"][name]'))
            .some(input => input.name === tempName && input.value !== '');
    }

    function prepareNewFileUpload(form, fileInput) {
        // Новый файл должен заменить ранее подготовленный temp-файл/флаг удаления.
        const tempName = getTempFieldName(fileInput.name);
        const removeNames = new Set([fileInput.name + '_remove']);
        const match = fileInput.name.match(/^(.*)\[([^\]]+)\]$/);
        if (match) {
            removeNames.add(`${match[1]}[remove]`);
            removeNames.add(`${match[1]}[${match[2]}_remove]`);
        }

        form.querySelectorAll('input[type="hidden"][name]').forEach(input => {
            if (input.name === tempName || removeNames.has(input.name)) {
                input.remove();
            }
        });
    }

    function checkFormValidity(form) {
        // После предварительной загрузки AJAX очищает file input и оставляет
        // hidden *_temp_id. Такой файл уже есть на сервере, поэтому required
        // у соответствующего input больше не должен блокировать повторную отправку.
        const relaxedInputs = [];
        form.querySelectorAll('input[type="file"][required][name]').forEach(input => {
            if (hasTempField(form, input)) {
                input.required = false;
                relaxedInputs.push(input);
            }
        });

        const valid = form.checkValidity();
        relaxedInputs.forEach(input => { input.required = true; });
        return valid;
    }

    function addStep(icon, text, state = 'pending') {
        const li = document.createElement('li');
        li.className = 'd-flex align-items-center mb-2';

        const stateSpan = document.createElement('span');
        stateSpan.className = 'me-2 step-state text-muted';
        const iconEl = document.createElement('i');
        iconEl.className = `bi ${icon}`;
        stateSpan.appendChild(iconEl);

        const textSpan = document.createElement('span');
        textSpan.textContent = text;

        li.append(stateSpan, textSpan);
        progressSteps.appendChild(li);
        return li;
    }

    function updateStep(step, text) {
        const iconEl = step.querySelector('.step-state i');
        if (iconEl) iconEl.className = 'bi bi-check-circle text-success';
        const textEl = step.querySelector('span:last-child');
        if (textEl) textEl.textContent = text;
    }

    function clearValidationErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(element => element.classList.remove('is-invalid'));
        form.querySelectorAll('[aria-invalid="true"]').forEach(element => element.removeAttribute('aria-invalid'));
        form.querySelectorAll('[data-ajax-error]').forEach(element => element.remove());
    }

    function dotKeyToBracketName(key) {
        return key.replace(/\.([^.\[\]]+)/g, '[$1]');
    }

    function findField(form, key) {
        const names = [key, dotKeyToBracketName(key)];
        for (const name of names) {
            const field = Array.from(form.elements).find(element => element.name === name);
            if (field) return field;
        }
        return null;
    }

    function showValidationErrors(form, errors) {
        clearValidationErrors(form);
        const list = document.createElement('ul');
        list.className = 'mb-0 ps-3';

        let firstInvalid = null;
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalizedMessages = Array.isArray(messages) ? messages : [messages];
            normalizedMessages.forEach(message => {
                const item = document.createElement('li');
                item.textContent = String(message);
                list.appendChild(item);
            });

            const field = findField(form, key);
            if (field) {
                field.classList.add('is-invalid');
                field.setAttribute('aria-invalid', 'true');
                if (!firstInvalid) firstInvalid = field;

                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block';
                feedback.dataset.ajaxError = '1';
                feedback.textContent = normalizedMessages.join(' ');
                field.insertAdjacentElement('afterend', feedback);
            }
        });

        progressErrors.replaceChildren();
        const title = document.createElement('div');
        title.className = 'fw-semibold mb-2';
        title.textContent = 'Проверьте заполнение формы:';
        progressErrors.append(title, list);
        progressErrors.classList.remove('d-none');

        if (firstInvalid) {
            setTimeout(() => firstInvalid.focus({ preventScroll: true }), 0);
        }
    }

    function showErrorMessage(message) {
        progressErrors.replaceChildren();
        const text = document.createElement('div');
        text.textContent = message;
        progressErrors.appendChild(text);
        progressErrors.classList.remove('d-none');
    }

    function openModal() {
        progressSteps.replaceChildren();
        progressErrors.replaceChildren();
        progressErrors.classList.add('d-none');
        setProgress(0);
        progressStatus.textContent = 'Подготовка...';
        closeBtn.classList.add('d-none');
        closeBtn.textContent = 'Закрыть';
        closeBtn.onclick = null;
        progressModal.show();
    }

    async function readResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            try {
                return await response.json();
            } catch (error) {
                throw new Error('Сервер вернул повреждённый JSON-ответ.');
            }
        }

        const text = await response.text();
        return text ? { message: text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() } : null;
    }

    function httpError(response, result) {
        if (response.status === 422 && result?.errors) {
            const error = new Error('Ошибка валидации');
            error.status = 422;
            error.errors = result.errors;
            return error;
        }

        const messages = {
            419: 'Сессия истекла. Обновите страницу и повторите действие.',
            403: 'Недостаточно прав для выполнения этого действия.',
            404: 'Запрошенный ресурс не найден.',
            413: 'Файл или запрос слишком большой.',
            500: 'Внутренняя ошибка сервера. Проверьте журнал Laravel.',
        };

        const error = new Error(result?.message || messages[response.status] || `Ошибка сервера (${response.status}).`);
        error.status = response.status;
        error.errors = result?.errors || null;
        return error;
    }

    async function fetchJson(url, options) {
        const response = await fetch(url, options);
        const result = await readResponse(response);
        if (!response.ok) throw httpError(response, result);
        return result;
    }

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('form[data-ajax-form]');
        if (!form) return;

        event.preventDefault();

        if (form.dataset.ajaxSubmitting === '1') return;

        if (!checkFormValidity(form)) {
            form.reportValidity();
            return;
        }

        const action = form.getAttribute('action');
        if (!action) {
            console.error('[FormProgressModal] AJAX form has no action.', form);
            return;
        }

        form.dataset.ajaxSubmitting = '1';
        const submitButtons = form.querySelectorAll('[type="submit"]');
        submitButtons.forEach(button => button.disabled = true);
        clearValidationErrors(form);
        openModal();

        try {
            addStep('bi-check-circle', 'Сбор данных...', 'done');
            setProgress(5);
            await new Promise(resolve => setTimeout(resolve, 50));

            addStep('bi-check-circle', 'Составление запроса...', 'done');
            setProgress(15);
            await new Promise(resolve => setTimeout(resolve, 50));

            const fileInputs = form.querySelectorAll('input[type="file"][name]');
            const files = [];
            fileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    files.push({ input, file: input.files[0] });
                }
            });

            const totalFiles = files.length;
            const uploadBaseUrl = '{{ route('files.upload') }}';
            let uploaded = 0;

            for (const { input, file } of files) {
                prepareNewFileUpload(form, input);
                uploaded++;
                const step = addStep('bi-upload', `Загрузка медиа (${uploaded} из ${totalFiles}): ${file.name}...`, 'active');
                progressStatus.textContent = `Загрузка медиа: ${uploaded} из ${totalFiles}`;

                if (file.size > CHUNK_THRESHOLD) {
                    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                    const chunkUrls = {
                        init: '{{ route('upload.chunk.init') }}',
                        upload: '{{ route('upload.chunk') }}',
                        finalize: '{{ route('upload.chunk.finalize') }}',
                    };

                    const initData = await fetchJson(chunkUrls.init, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            filename: file.name,
                            total_chunks: totalChunks,
                            chunk_size: CHUNK_SIZE,
                        }),
                    });

                    if (!initData?.upload_id) throw new Error('Ошибка инициализации чанковой загрузки.');

                    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                        const start = chunkIndex * CHUNK_SIZE;
                        const end = Math.min(start + CHUNK_SIZE, file.size);
                        const blob = file.slice(start, end);
                        const formData = new FormData();
                        formData.append('upload_id', initData.upload_id);
                        formData.append('chunk_index', chunkIndex);
                        formData.append('file', blob, file.name + '.part');

                        await fetchJson(chunkUrls.upload, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        updateStep(step, `Загрузка медиа (${uploaded} из ${totalFiles}): ${file.name} — чанк ${chunkIndex + 1}/${totalChunks}`);
                        setProgress(15 + (uploaded / totalFiles) * 60 * ((chunkIndex + 1) / totalChunks));
                    }

                    const finalizeData = await fetchJson(chunkUrls.finalize, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            upload_id: initData.upload_id,
                            filename: file.name,
                            total_chunks: totalChunks,
                        }),
                    });

                    if (!finalizeData?.temp_id || !finalizeData?.extension) {
                        throw new Error('Сервер не вернул данные временного файла.');
                    }

                    input.value = '';
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = getTempFieldName(input.name);
                    hidden.value = finalizeData.temp_id + '.' + finalizeData.extension;
                    form.appendChild(hidden);
                } else {
                    const data = new FormData();
                    data.append('file', file);
                    const result = await fetchJson(uploadBaseUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: data,
                    });

                    if (!result?.temp_id || !result?.extension) {
                        throw new Error('Сервер не вернул temp_id/extension.');
                    }

                    input.value = '';
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = getTempFieldName(input.name);
                    hidden.value = result.temp_id + '.' + result.extension;
                    form.appendChild(hidden);
                }

                updateStep(step, `Загрузка медиа (${uploaded} из ${totalFiles}): ${file.name} — завершено`);
            }

            setProgress(totalFiles === 0 ? 60 : 75);
            addStep('bi-send', 'Отправка формы...', 'active');
            progressStatus.textContent = 'Отправка формы...';

            const formData = new FormData(form);
            const methodInput = form.querySelector('input[name="_method"]');
            const declaredMethod = (form.getAttribute('method') || 'POST').toUpperCase();
            const method = methodInput ? methodInput.value.toUpperCase() : declaredMethod;

            // Laravel already receives _method from its hidden method-spoofing field.
            // Add it only when the form declares a non-POST method without such a field.
            if (method !== 'POST' && !formData.has('_method')) {
                formData.append('_method', method);
            }

            const result = await fetchJson(action, {
                // HTML forms are submitted through POST; Laravel method spoofing
                // carries PUT/PATCH/DELETE in _method when required.
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (result && result.success === false) {
                const error = new Error(result.message || 'Сервер отклонил запрос.');
                error.errors = result.errors || null;
                error.status = 422;
                throw error;
            }

            setProgress(100);
            progressStatus.textContent = result?.message || 'Форма успешно отправлена!';
            closeBtn.textContent = 'Ок';
            closeBtn.classList.remove('d-none');
            closeBtn.onclick = function () {
                if (result?.redirect) {
                    window.location.href = result.redirect;
                } else {
                    window.location.reload();
                }
            };
        } catch (error) {
            console.error('[FormProgressModal] error', error);
            setProgress(100);

            if (error?.status === 422 && error.errors) {
                progressStatus.textContent = 'Не удалось сохранить форму.';
                showValidationErrors(form, error.errors);
            } else {
                progressStatus.textContent = 'Не удалось выполнить запрос.';
                showErrorMessage(error?.message || 'Неизвестная ошибка.');
            }

            closeBtn.classList.remove('d-none');
            closeBtn.textContent = 'Закрыть';
            closeBtn.onclick = null;
        } finally {
            form.dataset.ajaxSubmitting = '0';
            submitButtons.forEach(button => button.disabled = false);
        }
    });
});
</script>
