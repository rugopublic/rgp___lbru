/**
 * DataUploader
 *
 * Visualizes the lifecycle of file and form-data processing.
 *
 * File lifecycle:
 *
 *     uploading
 *         ↓
 *     processing
 *         ↓
 *     completed
 *
 * On failure:
 *
 *     uploading / processing
 *         ↓
 *       failed
 *
 * Important:
 * 100% means that all file bytes have been uploaded.
 * It does not mean that server-side processing has completed.
 *
 * @class DataUploader
 */
class DataUploader
{
    /**
     * @param {HTMLElement} element
     */
    constructor(element)
    {
        if (!(element instanceof HTMLElement)) {
            throw new TypeError(
                'DataUploader requires an HTMLElement.'
            );
        }

        this.element = element;

        this.statusValue = null;
        this.files = null;
        this.log = null;
        this.logList = null;
    }

    /**
     * Binds DataUploader to a DOM element.
     *
     * @param {HTMLElement} element
     * @returns {DataUploader}
     */
    static on(element)
    {
        if (!(element instanceof HTMLElement)) {
            throw new TypeError(
                'DataUploader requires an HTMLElement.'
            );
        }

        if (!element.matches('[data-data-uploader]')) {
            throw new Error(
                'DataUploader element must have [data-data-uploader].'
            );
        }

        const instance = new DataUploader(element);

        instance._render();

        return instance;
    }

    /**
     * Renders the component.
     *
     * @returns {void}
     * @private
     */
    _render()
    {
        this.element.innerHTML = '';

        const status = document.createElement('div');
        status.className = 'data-uploader__status';

        const statusLabel = document.createElement('span');
        statusLabel.className = 'data-uploader__status-label';
        statusLabel.textContent = 'Статус:';

        this.statusValue = document.createElement('strong');
        this.statusValue.className = 'data-uploader__status-value';
        this.statusValue.textContent = 'Подготовка';

        status.append(
            statusLabel,
            this.statusValue
        );

        this.files = document.createElement('div');
        this.files.className = 'data-uploader__files';

        this.log = document.createElement('div');
        this.log.className = 'data-uploader__log';

        const logTitle = document.createElement('div');
        logTitle.className = 'data-uploader__log-title';
        logTitle.textContent = 'Ход операции:';

        this.logList = document.createElement('div');
        this.logList.className = 'data-uploader__log-list';

        this.log.append(
            logTitle,
            this.logList
        );

        this.element.append(
            status,
            this.files,
            this.log
        );
    }

    /**
     * Changes the main status.
     *
     * @param {string} status
     * @returns {DataUploader}
     */
    setStatus(status)
    {
        this.statusValue.textContent = status;

        return this;
    }

    /**
     * Starts file processing.
     *
     * @returns {DataUploader}
     */
    startFiles()
    {
        this.setStatus('Обработка файлов...');

        return this;
    }

    /**
     * Adds a file to the visual progress list.
     *
     * The file name, progress bar, processing state and
     * percentage belong to the same grid row.
     *
     * @param {string} name
     * @returns {DataUploader}
     */
    addFile(name)
    {
        if (this._getFile(name)) {
            throw new Error(
                `DataUploader file "${name}" already exists.`
            );
        }

        const item = document.createElement('div');
        item.className = 'data-uploader__file';
        item.dataset.file = name;

        const row = document.createElement('div');
        row.className = 'data-uploader__file-row';

        const label = document.createElement('div');
        label.className = 'data-uploader__file-name';
        label.textContent = name;
        label.title = name;

        const progressWrapper = document.createElement('div');
        progressWrapper.className = 'data-uploader__progress';

        const progress = document.createElement('div');
        progress.className = 'progress';
        progress.setAttribute('role', 'progressbar');
        progress.setAttribute(
            'aria-label',
            `Загрузка ${name}`
        );
        progress.setAttribute('aria-valuenow', '0');
        progress.setAttribute('aria-valuemin', '0');
        progress.setAttribute('aria-valuemax', '100');

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressBar.style.width = '0%';

        progress.append(progressBar);
        progressWrapper.append(progress);

        const state = document.createElement('div');
        state.className = 'data-uploader__file-state';
        state.setAttribute('aria-hidden', 'true');

        const percentage = document.createElement('div');
        percentage.className = 'data-uploader__file-percent';
        percentage.textContent = '0%';

        row.append(
            label,
            progressWrapper,
            state,
            percentage
        );

        item.append(row);

        this.files.append(item);

        return this;
    }

    /**
     * Updates byte-upload progress.
     *
     * 100% here means that all bytes have been sent.
     * The file may still be in server-side processing.
     *
     * @param {string} name
     * @param {number} percentage
     * @returns {DataUploader}
     */
    setFileProgress(name, percentage)
    {
        const item = this._getFile(name);

        if (!item) {
            throw new Error(
                `DataUploader file "${name}" does not exist.`
            );
        }

        const progress = item.querySelector(
            '[role="progressbar"]'
        );

        const progressBar = item.querySelector(
            '.progress-bar'
        );

        const percentageElement = item.querySelector(
            '.data-uploader__file-percent'
        );

        const value = Math.max(
            0,
            Math.min(100, Number(percentage))
        );

        const roundedValue = Math.round(value);

        progress.setAttribute(
            'aria-valuenow',
            String(roundedValue)
        );

        progressBar.style.width = `${value}%`;

        percentageElement.textContent =
            `${roundedValue}%`;

        if (value >= 100) {
            this.startFileProcessing(name);
        }

        return this;
    }

    /**
     * Marks a file as being processed by the server.
     *
     * @param {string} name
     * @returns {DataUploader}
     */
    startFileProcessing(name)
    {
        const item = this._getFile(name);

        if (!item) {
            throw new Error(
                `DataUploader file "${name}" does not exist.`
            );
        }

        item.classList.remove(
            'data-uploader__file--completed',
            'data-uploader__file--failed'
        );

        item.classList.add(
            'data-uploader__file--processing'
        );

        const state = item.querySelector(
            '.data-uploader__file-state'
        );

        state.textContent = '';

        return this;
    }

    /**
     * Marks a file as successfully completed.
     *
     * @param {string} name
     * @returns {DataUploader}
     */
    completeFile(name)
    {
        const item = this._getFile(name);

        if (!item) {
            throw new Error(
                `DataUploader file "${name}" does not exist.`
            );
        }

        this.setFileProgress(name, 100);

        item.classList.remove(
            'data-uploader__file--processing',
            'data-uploader__file--failed'
        );

        item.classList.add(
            'data-uploader__file--completed'
        );

        const state = item.querySelector(
            '.data-uploader__file-state'
        );

        state.textContent = '✓';

        return this;
    }

    /**
     * Marks a file as failed.
     *
     * @param {string} name
     * @returns {DataUploader}
     */
    failFile(name)
    {
        const item = this._getFile(name);

        if (!item) {
            throw new Error(
                `DataUploader file "${name}" does not exist.`
            );
        }

        item.classList.remove(
            'data-uploader__file--processing',
            'data-uploader__file--completed'
        );

        item.classList.add(
            'data-uploader__file--failed'
        );

        const state = item.querySelector(
            '.data-uploader__file-state'
        );

        state.textContent = '×';

        return this;
    }

    /**
     * Starts form-data submission.
     *
     * @returns {DataUploader}
     */
    startForm()
    {
        this.setStatus('Загрузка данных формы...');

        return this;
    }

    /**
     * Adds a secondary operation message.
     *
     * Technical identifiers such as upload_id must not be
     * displayed here.
     *
     * @param {string} message
     * @returns {DataUploader}
     */
    logMessage(message)
    {
        const item = document.createElement('div');

        item.className = 'data-uploader__log-message';
        item.textContent = message;

        this.logList.append(item);

        this._scrollLogToBottom();

        return this;
    }

    /**
     * Marks the complete operation as successful.
     *
     * @returns {DataUploader}
     */
    complete()
    {
        this.setStatus('Готово');

        return this;
    }

    /**
     * Marks the complete operation as failed.
     *
     * @param {string|null} [message]
     * @returns {DataUploader}
     */
    fail(message = null)
    {
        this.setStatus('Ошибка');

        if (message) {
            this.logMessage(message);
        }

        return this;
    }

    /**
     * Finds a file element by its original file name.
     *
     * @param {string} name
     * @returns {HTMLElement|null}
     * @private
     */
    _getFile(name)
    {
        return this.files.querySelector(
            `[data-file="${CSS.escape(name)}"]`
        );
    }

    /**
     * Scrolls the operation log to the bottom.
     *
     * @returns {void}
     * @private
     */
    _scrollLogToBottom()
    {
        this.log.scrollTop = this.log.scrollHeight;
    }
}

export default DataUploader;
