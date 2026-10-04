/**
 * FormSender
 *
 * Handles AJAX form submission together with:
 * - ChangeTracker payload collection;
 * - ordinary input[type="file"] uploads;
 * - RichTextarea pending image uploads;
 * - replacement of RichTextarea temporary file tokens with upload IDs;
 * - final AJAX request dispatch.
 */

import api from "../../../http/api";
import fileUploader from "../../../http/tools/fileUploader";
import DataUploader from "../../http/data-uploader";

class FormSender {
    /**
     * Creates a new FormSender instance.
     *
     * @param {HTMLFormElement} form
     */
    constructor(form) {
        if (!(form instanceof HTMLFormElement)) {
            throw new TypeError(
                "The 'form' must be a valid HTML form node."
            );
        }

        const tracker = form.__changeTracker;

        if (!tracker) {
            throw new TypeError(
                "The AJAX form must have a ChangeTracker."
            );
        }

        const uploaderElement = document.querySelector(
            '#formSenderModal [data-data-uploader]'
        );

        if (!(uploaderElement instanceof HTMLElement)) {
            throw new TypeError(
                "The FormSender modal must contain a DataUploader element."
            );
        }

        this.form = form;
        this.tracker = tracker;
        this.action = form.action;

        /**
         * Current form payload.
         *
         * This is initially a snapshot from ChangeTracker.
         * It is refreshed after RichTextarea upload resolution.
         *
         * @type {Object}
         */
        this.payload = tracker.payload;

        this.uploader = DataUploader.on(
            uploaderElement
        );

        const methodField = form.querySelector(
            'input[name="_method"]'
        );

        this.method = (
            methodField?.value
            ?? form.method
            ?? 'POST'
        ).toUpperCase();
    }

    /**
     * Opens the form sender progress modal.
     *
     * @returns {void}
     */
    openModal() {
        const modalElement =
            document.getElementById(
                'formSenderModal'
            );

        if (!(modalElement instanceof HTMLElement)) {
            throw new TypeError(
                "The 'formSenderModal' element was not found."
            );
        }

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );

        modal.show();

        this.uploader.setStatus(
            'Подготовка...'
        );

        this.uploader.logMessage(
            'Подготовка изменений формы...'
        );
    }

    /**
     * Displays backend validation errors inside the sender modal.
     *
     * @param {Object<string, string[]>} errors
     *
     * @returns {void}
     */
    showValidationErrors(errors) {
        const modalElement =
            document.getElementById(
                'formSenderModal'
            );

        const errorsElement =
            modalElement.querySelector(
                '[data-sender-errors]'
            );

        const list =
            document.createElement('ul');

        list.className =
            'mb-0 ps-3';

        Object.values(errors).forEach(
            messages => {
                for (const message of messages) {
                    const item =
                        document.createElement('li');

                    item.textContent =
                        String(message);

                    list.append(item);
                }
            }
        );

        errorsElement.replaceChildren(
            list
        );

        errorsElement.classList.remove(
            'd-none'
        );
    }

    /**
     * Returns all initialized RichTextarea instances belonging to this form.
     *
     * RichTextarea exposes its controller on the real textarea through
     * `__richTextarea`. This keeps FormSender independent from the
     * RichTextarea implementation and avoids a second global registry.
     *
     * @returns {Array<Object>}
     */
    getRichTextareas() {
        return Array.from(
            this.form.querySelectorAll(
                'textarea[data-rich-textarea]'
            )
        )
            .map(
                textarea =>
                    textarea.__richTextarea ?? null
            )
            .filter(
                instance => instance !== null
            );
    }

    /**
     * Uploads one file through the shared file upload pipeline.
     *
     * @param {File} file
     *
     * @returns {Promise<string>}
     */
    async uploadFile(file) {
        console.log(
            `Uploading single file: ${file.name}...`
        );

        this.uploader.addFile(
            file.name
        );

        try {
            const {
                upload_id: uploadId,
            } = await fileUploader.upload(
                file,
                {
                    onProgress: ({
                        percentage,
                    }) => {
                        this.uploader.setFileProgress(
                            file.name,
                            percentage
                        );
                    },
                }
            );

            this.uploader.completeFile(
                file.name
            );

            this.uploader.logMessage(
                `Файл «${file.name}» загружен.`
            );

            return uploadId;
        } catch (error) {
            this.uploader.failFile(
                file.name
            );

            this.uploader.logMessage(
                `Не удалось загрузить файл «${file.name}».`
            );

            throw error;
        }
    }

    /**
     * Uploads ordinary files already present in the ChangeTracker payload.
     *
     * Files inside the payload are replaced in-place with their generated
     * upload IDs.
     *
     * This preserves the existing file upload behavior for:
     * - input[type="file"];
     * - nested payload objects;
     * - arrays containing File instances.
     *
     * @returns {Promise<void>}
     */
    async uploadFiles() {
        const scanAndReplace = async (node) => {
            if (
                !node
                || typeof node !== 'object'
            ) {
                return;
            }

            for (
                const key of Object.keys(node)
            ) {
                const value = node[key];

                /*
                 * Existing ChangeTracker file descriptors are normally
                 * unwrapped into File[] by tracker.payload.
                 *
                 * Preserve support for a single-file array.
                 */
                if (
                    Array.isArray(value)
                    && value.length === 1
                    && value[0] instanceof File
                ) {
                    node[key] =
                        await this.uploadFile(
                            value[0]
                        );

                    continue;
                }

                if (value instanceof File) {
                    node[key] =
                        await this.uploadFile(
                            value
                        );

                    continue;
                }

                if (
                    typeof value === 'object'
                    && Array.isArray(value)
                ) {
                    for (
                        let index = 0;
                        index < value.length;
                        index++
                    ) {
                        if (
                            value[index]
                            instanceof File
                        ) {
                            value[index] =
                                await this.uploadFile(
                                    value[index]
                                );
                        } else {
                            await scanAndReplace(
                                value[index]
                            );
                        }
                    }

                    continue;
                }

                if (
                    typeof value === 'object'
                ) {
                    await scanAndReplace(
                        value
                    );
                }
            }
        };

        await scanAndReplace(
            this.payload
        );
    }

    /**
     * Uploads all pending image files owned by RichTextarea instances.
     *
     * Each RichTextarea exposes:
     *
     * getPendingFiles()
     *
     * returning:
     *
     * [
     *     {
     *         token: string,
     *         file: File
     *     }
     * ]
     *
     * Uploaded files are collected into a temporary token -> upload ID map
     * for their respective RichTextarea instance.
     *
     * @returns {Promise<void>}
     */
    async uploadRichTextareas() {
        const richTextareas =
            this.getRichTextareas();

        for (
            const richTextarea
                of richTextareas
        ) {
            const pendingFiles =
                richTextarea.getPendingFiles();

            if (
                !Array.isArray(pendingFiles)
                || pendingFiles.length === 0
            ) {
                continue;
            }

            const uploads = {};

            for (
                const pendingFile
                    of pendingFiles
            ) {
                if (
                    !pendingFile
                    || typeof pendingFile.token
                        !== 'string'
                    || !(pendingFile.file
                        instanceof File)
                ) {
                    continue;
                }

                const uploadId =
                    await this.uploadFile(
                        pendingFile.file
                    );

                uploads[
                    pendingFile.token
                ] = uploadId;
            }

            if (
                Object.keys(uploads).length === 0
            ) {
                continue;
            }

            richTextarea.resolveUploads(
                uploads
            );
        }
    }

    /**
     * Refreshes the payload from the current ChangeTracker state.
     *
     * This is required after RichTextarea resolves temporary file tokens
     * into server-generated upload IDs because RichTextarea emits a native
     * input event and ChangeTracker therefore updates its state.
     *
     * @returns {void}
     */
    refreshPayload() {
        this.payload =
            this.tracker.payload;
    }

    /**
     * Sends the form through the complete AJAX submission pipeline.
     *
     * Processing order:
     *
     * 1. Start file processing UI;
     * 2. Upload ordinary files;
     * 3. Upload pending RichTextarea files;
     * 4. Refresh ChangeTracker payload;
     * 5. Send the final payload to the backend.
     *
     * @returns {Promise<void>}
     */
    async send() {
        try {
            this.uploader.startFiles();

            this.uploader.logMessage(
                'Загрузка файлов...'
            );

            /*
             * Existing form file inputs.
             */
            await this.uploadFiles();

            /*
             * RichTextarea-local files.
             */
            await this.uploadRichTextareas();

            /*
             * RichTextarea.resolveUploads() emits an input event,
             * therefore ChangeTracker now contains HTML with
             * data-upload-id attributes.
             *
             * Rebuild the snapshot before submitting.
             */
            this.refreshPayload();

            this.uploader.startForm();

            this.uploader.logMessage(
                'Файлы загружены. Отправка данных формы...'
            );

            const response =
                await api.request({
                    method: this.method,
                    url: this.action,
                    data: this.payload,
                });

            if (response.data.success) {
                setTimeout(() => {
                    window.location.href =
                        response.data.redirect;
                }, 3500);
            }
        } catch (error) {
            if (
                error.response?.status === 422
            ) {
                this.uploader.fail(
                    'Не удалось сохранить изменения.'
                );

                this.showValidationErrors(
                    error.response.data?.errors ?? {}
                );

                return;
            }

            this.uploader.fail(
                error.response?.data?.message
                ?? 'Не удалось выполнить запрос.'
            );
        }
    }
}

document.addEventListener(
    'DOMContentLoaded',
    () => {
        document.addEventListener(
            'submit',
            async function (event) {
                if (
                    event.target
                        instanceof HTMLFormElement
                    && event.target.hasAttribute(
                        'data-ajax-form'
                    )
                ) {
                    event.preventDefault();

                    const sender =
                        new FormSender(
                            event.target
                        );

                    sender.openModal();

                    await sender.send();
                }
            }
        );
    }
);
