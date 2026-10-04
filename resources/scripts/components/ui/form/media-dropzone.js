/**
 * @file resources/scripts/components/ui/form/media-dropzone.js
 * @description Reusable browser component module for ui/form/media-dropzone.js; owns the client-side behavior of this UI primitive.
 */

import { Modal } from 'bootstrap';
import Cropper from 'cropperjs';

class MediaDropzone {
    constructor(element) {
        this.element = element;

        // =========================================================================
        // DOM
        // =========================================================================

        this.input = element.querySelector('[data-media-input]');
        this.message = element.querySelector('[data-media-message]');
        this.surface = element.querySelector('[data-media-dropzone-area]');

        this.fileInfo = element.querySelector('[data-media-file]');
        this.fileName = element.querySelector('[data-media-file-name]');
        this.fileSize = element.querySelector('[data-media-file-size]');

        this.preview = element.querySelector('[data-media-preview]');
        this.imagePreview = element.querySelector(
            '[data-media-image-preview]',
        );
        this.videoPreview = element.querySelector(
            '[data-media-video-preview]',
        );

        this.removeButton = element.querySelector('[data-media-remove]');

        // -------------------------------------------------------------------------
        // Cropper modal
        // -------------------------------------------------------------------------

        this.cropperModalElement = element.querySelector(
            '[data-media-cropper-modal]',
        );

        this.cropperModal = this.cropperModalElement
            ? new Modal(this.cropperModalElement)
            : null;

        this.cropperContainer = element.querySelector(
            '[data-media-cropper-container]',
        );

        this.cropperApplyButton =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-apply]',
            );

        this.cropperCancelButton =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-cancel]',
            );

        this.cropperResetButton =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-reset]',
            );

        this.cropperFitWidthButton =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-fit-width]',
            );

        this.cropperFitHeightButton =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-fit-height]',
            );

        // -------------------------------------------------------------------------
        // Live preview
        // -------------------------------------------------------------------------

        this.cropperPreview = this.cropperModalElement?.querySelector(
            '[data-media-cropper-preview]',
        );

        this.cropperPreviewCanvas =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-preview-canvas]',
            );

        this.cropperPreviewPlaceholder =
            this.cropperModalElement?.querySelector(
                '[data-media-cropper-preview-placeholder]',
            );

        // =========================================================================
        // Configuration
        // =========================================================================

        this.name = element.dataset.name ?? '';
        this.ratio = element.dataset.ratio ?? '';
        this.existingUrl = element.dataset.existingUrl ?? null;

        this.isExisting = false;

        this.imageMaxSize = this.parsePositiveNumber(
            element.dataset.imageMaxSize,
        );

        this.videoMaxSize = this.parsePositiveNumber(
            element.dataset.videoMaxSize,
        );

        this.imageWidth = this.parsePositiveNumber(
            element.dataset.imageWidth,
        );

        this.imageHeight = this.parsePositiveNumber(
            element.dataset.imageHeight,
        );

        this.imageQuality = Number(element.dataset.imageQuality);

        // =========================================================================
        // State
        // =========================================================================

        this.state = 'idle';

        /*
         * this.file всегда содержит файл, который сейчас выбран пользователем.
         *
         * Пока cropper-модаль открыта, здесь находится ОРИГИНАЛЬНЫЙ File.
         *
         * Только Apply заменяет его на результирующий JPEG.
         */
        this.file = null;

        // =========================================================================
        // Cropper state
        // =========================================================================

        this.cropper = null;
        this.cropperCanvas = null;
        this.cropperImage = null;
        this.cropperSelection = null;

        this.objectUrl = null;

        // =========================================================================
        // Preview state
        // =========================================================================

        this.previewObjectUrl = null;
        this.videoPreviewObjectUrl = null;

        /*
         * CropperSelection.$change() сообщает change до окончательной
         * фиксации geometry. Поэтому preview рендерится через RAF.
         */
        this.previewFrameRequest = null;

        /*
         * Защита от устаревших асинхронных $toCanvas().
         */
        this.previewRenderSequence = 0;

        /*
         * Флаг уничтожения cropper.
         */
        this.isDestroying = false;

        // =========================================================================
        // Events
        // =========================================================================

        this.bindEvents();

        // =========================================================================
        // Existing file
        // =========================================================================

        if (this.existingUrl) {
            this.showExistingFile(this.existingUrl);
        }

        console.debug('[MediaDropzone] initialized', {
            name: this.name,
            ratio: this.ratio,
            existingUrl: this.existingUrl,
            imageMaxSize: this.imageMaxSize,
            videoMaxSize: this.videoMaxSize,
            imageWidth: this.imageWidth,
            imageHeight: this.imageHeight,
            imageQuality: this.imageQuality,
            state: this.state,
        });
    }

    clearRemoveSignal() {
        if (!this.element || !this.name) return;

        try {
            const escapedName = CSS.escape(this.name);
            const removeField = this.element.querySelector(
                `input[name="${escapedName}_remove"]`,
            );

            if (removeField) {
                console.debug('[MediaDropzone] New file selected. Canceling previous remote deletion signal.');

                const tracker = removeField._valueTracker;
                if (tracker) {
                    tracker.setValue('');
                } else {
                    removeField.value = '';
                }

                let changeEvent;
                try {
                    changeEvent = new Event('change', { bubbles: true });
                } catch (e) {
                    changeEvent = document.createEvent('Event');
                    changeEvent.initEvent('change', true, false);
                }
                removeField.dispatchEvent(changeEvent);

                removeField.remove();
            }
        } catch (error) {
            console.error('[MediaDropzone] Failed to safely cycle out the removal token state:', error);
        }
    }

    // =========================================================================
    // Events
    // =========================================================================

    bindEvents() {
        this.input?.addEventListener('change', (event) => {
            this.handleFileSelection(event);
        });

        this.removeButton?.addEventListener('click', () => {
            this.removeFile();
        });

        // -------------------------------------------------------------------------
        // Cropper modal lifecycle
        // -------------------------------------------------------------------------

        this.cropperModalElement?.addEventListener(
            'shown.bs.modal',
            () => {
                this.initializeCropper();
            },
        );

        this.cropperModalElement?.addEventListener(
            'hidden.bs.modal',
            () => {
                this.destroyCropper();
            },
        );

        // -------------------------------------------------------------------------
        // Cropper selection
        // -------------------------------------------------------------------------

        this.cropperContainer?.addEventListener(
            'change',
            (event) => {
                this.handleCropSelectionChange(event);
            },
        );

        // -------------------------------------------------------------------------
        // Cropper actions
        // -------------------------------------------------------------------------

        this.cropperApplyButton?.addEventListener('click', () => {
            this.applyCrop();
        });

        this.cropperCancelButton?.addEventListener('click', () => {
            this.cancelCrop();
        });

        this.cropperResetButton?.addEventListener('click', () => {
            this.resetCrop();
        });

        this.cropperFitWidthButton?.addEventListener('click', () => {
            this.fitSelectionToWidth();
        });

        this.cropperFitHeightButton?.addEventListener('click', () => {
            this.fitSelectionToHeight();
        });
    }

    // =========================================================================
    // File selection
    // =========================================================================

    async handleFileSelection(event) {
        if (this._isProgrammaticSync) {
            return;
        }

        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        console.debug('[MediaDropzone] file selected', {
            name: this.name,
            file: file.name,
            type: file.type,
            size: file.size,
        });

        this.isExisting = false;
        this.existingUrl = null;

        this.clearError();

        this.file = file;
        this.setState('selected');

        const isValid = this.validateFile();

        if (!isValid) {
            this.clearFileInput();
            return;
        }

        if (file.type.startsWith('image/')) {
            this.openCropperModal();
            return;
        }

        this.updateFileInfo(file);

        if (file.type.startsWith('video/')) {
            this.showVideoPreview(file);
        }

        this.setState('selected');
    }

    // =========================================================================
    // Validation
    // =========================================================================

    validateFile() {
        this.setState('validating');

        if (!this.file) {
            this.setError('Файл не выбран.');
            return false;
        }

        if (!this.isAcceptedType(this.file)) {
            this.setError(
                `Тип файла "${this.file.type || 'неизвестен'}" не поддерживается.`,
            );

            console.debug('[MediaDropzone] validation failed', {
                name: this.name,
                file: this.file.name,
                type: this.file.type,
                reason: 'mime',
            });

            return false;
        }

        const maxSize = this.getMaxSize(this.file.type);

        if (maxSize !== null && this.file.size > maxSize) {
            this.setError(
                `Файл слишком большой. Максимальный размер: ${this.formatFileSize(maxSize)}.`,
            );

            console.debug('[MediaDropzone] validation failed', {
                name: this.name,
                file: this.file.name,
                type: this.file.type,
                size: this.file.size,
                maxSize,
                reason: 'size',
            });

            return false;
        }

        this.clearError();
        this.setState('selected');

        console.debug('[MediaDropzone] validation passed', {
            name: this.name,
            file: this.file.name,
            type: this.file.type,
            size: this.file.size,
            maxSize,
        });

        return true;
    }

    isAcceptedType(file) {
        const acceptAttribute = this.input?.getAttribute('accept');

        if (!acceptAttribute?.trim()) {
            return true;
        }

        const acceptedTypes = acceptAttribute
            .split(',')
            .map((type) => type.trim().toLowerCase())
            .filter(Boolean);

        if (!acceptedTypes.length) {
            return true;
        }

        const fileType = (file.type || '').toLowerCase();
        const fileName = file.name.toLowerCase();

        return acceptedTypes.some((acceptedType) => {
            // MIME type
            if (acceptedType === fileType) {
                return true;
            }

            // Wildcard MIME type
            if (
                acceptedType.endsWith('/*') &&
                fileType.startsWith(acceptedType.slice(0, -1))
            ) {
                return true;
            }

            // Extension
            if (acceptedType.startsWith('.')) {
                return fileName.endsWith(acceptedType);
            }

            return false;
        });
    }

    getMaxSize(type) {
        if (type.startsWith('image/')) {
            return Number.isFinite(this.imageMaxSize)
                ? this.imageMaxSize
                : null;
        }

        if (type.startsWith('video/')) {
            return Number.isFinite(this.videoMaxSize)
                ? this.videoMaxSize
                : null;
        }

        return null;
    }

    // =========================================================================
    // Existing file
    // =========================================================================

    showExistingFile(url) {
        console.debug('[MediaDropzone] showing existing file', {
            name: this.name,
            url,
        });

        this.isExisting = true;
        this.file = null;

        const cleanUrl = url.split('?')[0].split('#')[0];

        const isImage =
            /\.(jpg|jpeg|png|gif|webp|svg|bmp)$/i.test(cleanUrl);

        const isVideo =
            /\.(mp4|webm|ogg|mov|avi)$/i.test(cleanUrl);

        const fileName =
            cleanUrl.split('/').pop() || 'Файл';

        if (isImage) {
            this.clearImagePreview();
            this.clearVideoPreview();

            const image = document.createElement('img');

            image.src = url;
            image.alt = 'Existing image';
            image.classList.add('media-dropzone__image');

            this.imagePreview?.replaceChildren(image);

            if (this.imagePreview) {
                this.imagePreview.hidden = false;
            }

            if (this.preview) {
                this.preview.hidden = false;
            }

            if (this.fileName) {
                this.fileName.textContent = fileName;
            }

            if (this.fileSize) {
                this.fileSize.textContent = '';
            }

            if (this.fileInfo) {
                this.fileInfo.hidden = false;
            }

            if (this.removeButton) {
                this.removeButton.hidden = false;
            }

            this.setState('existing');

            return;
        }

        if (isVideo) {
            this.clearImagePreview();
            this.clearVideoPreview();

            const video = document.createElement('video');

            video.src = url;
            video.controls = true;
            video.preload = 'metadata';
            video.classList.add('media-dropzone__video');

            this.videoPreview?.replaceChildren(video);

            if (this.videoPreview) {
                this.videoPreview.hidden = false;
            }

            if (this.preview) {
                this.preview.hidden = false;
            }

            if (this.fileName) {
                this.fileName.textContent = fileName;
            }

            if (this.fileSize) {
                this.fileSize.textContent = '';
            }

            if (this.fileInfo) {
                this.fileInfo.hidden = false;
            }

            if (this.removeButton) {
                this.removeButton.hidden = false;
            }

            this.setState('existing');

            return;
        }

        console.warn(
            '[MediaDropzone] existing file type could not be detected',
            {
                name: this.name,
                url,
            },
        );
    }

    // =========================================================================
    // Cropper modal
    // =========================================================================

    openCropperModal() {
        if (!this.cropperModal) {
            console.warn(
                '[MediaDropzone] cropper modal is not available',
                {
                    name: this.name,
                },
            );

            return;
        }

        if (!this.cropperContainer) {
            console.warn(
                '[MediaDropzone] cropper container is not available',
                {
                    name: this.name,
                },
            );

            return;
        }

        this.clearCropperPreview();

        console.debug('[MediaDropzone] opening cropper modal', {
            name: this.name,
            file: this.file?.name,
        });

        this.cropperModal.show();
    }

    // =========================================================================
    // Cropper initialization
    // =========================================================================

    initializeCropper() {
        if (!this.file || !this.file.type.startsWith('image/')) {
            return;
        }

        if (!this.cropperContainer) {
            return;
        }

        this.isDestroying = false;

        // -------------------------------------------------------------------------
        // Cleanup
        // -------------------------------------------------------------------------

        this.destroyCropper();

        this.isDestroying = false;

        // -------------------------------------------------------------------------
        // Object URL
        // -------------------------------------------------------------------------

        this.objectUrl = URL.createObjectURL(this.file);

        // -------------------------------------------------------------------------
        // Cropper.js 2.x native template
        // -------------------------------------------------------------------------

        const template = `
            <cropper-canvas
                background
                translatable
                view-mode="1"
            >
                <cropper-image
                    rotatable="false"
                    scalable="false"
                    translatable
                ></cropper-image>

                <cropper-selection
                    id="mediaCropperSelection"
                    initial-coverage="0.9"
                    movable
                    resizable
                    zoomable="false"
                    outlined
                >
                    <cropper-grid
                        rows="3"
                        columns="3"
                        bordered
                        covered
                    ></cropper-grid>

                    <cropper-crosshair
                        centered
                    ></cropper-crosshair>

                    <cropper-handle
                        action="move"
                        theme-color="rgba(255, 255, 255, 0.35)"
                    ></cropper-handle>

                    <cropper-handle action="n-resize"></cropper-handle>
                    <cropper-handle action="e-resize"></cropper-handle>
                    <cropper-handle action="s-resize"></cropper-handle>
                    <cropper-handle action="w-resize"></cropper-handle>

                    <cropper-handle action="ne-resize"></cropper-handle>
                    <cropper-handle action="nw-resize"></cropper-handle>
                    <cropper-handle action="se-resize"></cropper-handle>
                    <cropper-handle action="sw-resize"></cropper-handle>
                </cropper-selection>
            </cropper-canvas>
        `;

        this.cropperContainer.innerHTML = template;

        // -------------------------------------------------------------------------
        // Native elements
        // -------------------------------------------------------------------------

        const canvas = this.cropperContainer.querySelector(
            'cropper-canvas',
        );

        const image = this.cropperContainer.querySelector(
            'cropper-image',
        );

        const selection = this.cropperContainer.querySelector(
            '#mediaCropperSelection',
        );

        if (!canvas || !image || !selection) {
            console.error(
                '[MediaDropzone] failed to create native Cropper.js structure',
                {
                    name: this.name,
                },
            );

            this.cropperContainer.replaceChildren();
            this.revokeCropperObjectUrl();

            return;
        }

        // -------------------------------------------------------------------------
        // Image
        // -------------------------------------------------------------------------

        image.src = this.objectUrl;
        image.alt = this.file.name;

        // -------------------------------------------------------------------------
        // Aspect ratio
        // -------------------------------------------------------------------------

        const aspectRatio = this.parseRatio(this.ratio);

        if (aspectRatio !== null) {
            selection.aspectRatio = aspectRatio;
            selection.initialAspectRatio = aspectRatio;
        }

        // -------------------------------------------------------------------------
        // Store elements
        // -------------------------------------------------------------------------

        this.cropperCanvas = canvas;
        this.cropperImage = image;
        this.cropperSelection = selection;

        // -------------------------------------------------------------------------
        // Direct change listener
        // -------------------------------------------------------------------------

        this.cropperSelection.addEventListener(
            'change',
            (event) => {
                this.handleCropSelectionChange(event);
            },
        );

        // -------------------------------------------------------------------------
        // Compatibility wrapper
        // -------------------------------------------------------------------------

        this.cropper = {
            getCropperCanvas: () => this.cropperCanvas,
            getCropperImage: () => this.cropperImage,
            getCropperSelection: () => this.cropperSelection,
        };

        // -------------------------------------------------------------------------
        // Wait for layout
        // -------------------------------------------------------------------------

        requestAnimationFrame(() => {
            if (
                !this.cropperSelection ||
                !this.cropperImage ||
                !this.objectUrl
            ) {
                return;
            }

            if (typeof this.cropperImage.$center === 'function') {
                this.cropperImage.$center();
            }

            if (
                typeof this.cropperSelection.$center ===
                'function'
            ) {
                this.cropperSelection.$center();
            }

            /*
             * После появления modal browser уже знает её размеры.
             * Даём ещё один кадр для окончательной геометрии.
             */
            requestAnimationFrame(() => {
                if (
                    this.isDestroying ||
                    !this.cropperSelection
                ) {
                    return;
                }

                this.schedulePreviewRender();
            });

            console.debug(
                '[MediaDropzone] native cropper initialized',
                {
                    name: this.name,
                    file: this.file.name,
                    ratio: this.ratio,
                    aspectRatio:
                        this.cropperSelection.aspectRatio,
                    movable:
                        this.cropperSelection.movable,
                    resizable:
                        this.cropperSelection.resizable,
                    zoomable:
                        this.cropperSelection.zoomable,
                },
            );
        });
    }

    // =========================================================================
    // Crop selection change
    // =========================================================================

    handleCropSelectionChange(event) {
        if (this.isDestroying) {
            return;
        }

        const selection = this.cropperSelection;

        if (!selection) {
            return;
        }

        const next = event.detail;

        if (!next) {
            return;
        }

        const {
            x,
            y,
            width,
            height,
        } = next;

        if (
            !Number.isFinite(x) ||
            !Number.isFinite(y) ||
            !Number.isFinite(width) ||
            !Number.isFinite(height) ||
            width <= 0 ||
            height <= 0
        ) {
            event.preventDefault();
            return;
        }

        this.schedulePreviewRender();
    }

    // =========================================================================
    // Live crop preview
    // =========================================================================

    schedulePreviewRender() {
        if (!this.cropperSelection) {
            return;
        }

        if (this.previewFrameRequest !== null) {
            cancelAnimationFrame(
                this.previewFrameRequest,
            );
        }

        this.previewFrameRequest = requestAnimationFrame(() => {
            this.previewFrameRequest = null;

            this.renderCropPreview();
        });
    }

    async renderCropPreview() {
        const selection = this.cropperSelection;

        if (!selection) {
            return;
        }

        if (
            !this.cropperPreviewCanvas ||
            !this.cropperPreview
        ) {
            return;
        }

        if (
            !Number.isFinite(this.imageWidth) ||
            this.imageWidth <= 0 ||
            !Number.isFinite(this.imageHeight) ||
            this.imageHeight <= 0
        ) {
            return;
        }

        if (typeof selection.$toCanvas !== 'function') {
            console.warn(
                '[MediaDropzone] CropperSelection.$toCanvas() is unavailable',
                {
                    name: this.name,
                },
            );

            return;
        }

        const renderSequence =
            ++this.previewRenderSequence;

        try {
            const canvas = await selection.$toCanvas({
                width: this.imageWidth,
                height: this.imageHeight,
            });

            if (
                renderSequence !==
                    this.previewRenderSequence ||
                this.cropperSelection !== selection ||
                this.isDestroying
            ) {
                return;
            }

            this._mountCanvasToPreview(canvas);
        } catch (error) {
            if (this.isDestroying) {
                return;
            }

            console.error(
                '[MediaDropzone] crop preview rendering failed',
                {
                    name: this.name,
                    error,
                },
            );
        }
    }

    _mountCanvasToPreview(canvas) {
        const previewCanvas =
            this.cropperPreviewCanvas;

        if (!previewCanvas) {
            return;
        }

        previewCanvas.width = canvas.width;
        previewCanvas.height = canvas.height;

        const context =
            previewCanvas.getContext('2d');

        if (!context) {
            return;
        }

        context.clearRect(
            0,
            0,
            previewCanvas.width,
            previewCanvas.height,
        );

        context.drawImage(
            canvas,
            0,
            0,
            canvas.width,
            canvas.height,
        );

        previewCanvas.hidden = false;

        if (this.cropperPreviewPlaceholder) {
            this.cropperPreviewPlaceholder.hidden = true;
        }

        if (this.cropperPreview) {
            this.cropperPreview.hidden = false;
        }
    }

    clearCropperPreview() {
        if (this.previewFrameRequest !== null) {
            cancelAnimationFrame(
                this.previewFrameRequest,
            );

            this.previewFrameRequest = null;
        }

        this.previewRenderSequence++;

        if (this.cropperPreviewCanvas) {
            const context =
                this.cropperPreviewCanvas.getContext('2d');

            if (context) {
                context.clearRect(
                    0,
                    0,
                    this.cropperPreviewCanvas.width,
                    this.cropperPreviewCanvas.height,
                );
            }

            this.cropperPreviewCanvas.hidden = true;
        }

        if (this.cropperPreviewPlaceholder) {
            this.cropperPreviewPlaceholder.hidden = false;
        }

        if (this.cropperPreview) {
            this.cropperPreview.hidden = false;
        }
    }

    // =========================================================================
    // Reset crop
    // =========================================================================

    resetCrop() {
        const selection = this.cropperSelection;

        if (!selection) {
            return;
        }

        if (typeof selection.$reset !== 'function') {
            console.warn(
                '[MediaDropzone] CropperSelection.$reset() is unavailable',
                {
                    name: this.name,
                },
            );

            return;
        }

        selection.$reset();

        this.schedulePreviewRender();
    }

    // =========================================================================
    // Fit selection to image width
    // =========================================================================

    fitSelectionToWidth() {
        const selection = this.cropperSelection;
        const image = this.cropperImage;

        if (!selection || !image) {
            return;
        }

        if (typeof image.$ready === 'function') {
            image.$ready()
                .then(() => {
                    this._fitSelectionToWidth(
                        selection,
                        image,
                    );
                })
                .catch(() => {
                    this._fitSelectionToWidth(
                        selection,
                        image,
                    );
                });

            return;
        }

        this._fitSelectionToWidth(selection, image);
    }

    _fitSelectionToWidth(selection, image) {
        const container = this.cropperContainer;

        if (!container) {
            return;
        }

        const containerRect =
            container.getBoundingClientRect();

        const imageRect =
            image.getBoundingClientRect();

        const imageX =
            imageRect.left -
            containerRect.left;

        const imageY =
            imageRect.top -
            containerRect.top;

        const imageWidth = imageRect.width;
        const imageHeight = imageRect.height;

        if (
            imageWidth <= 0 ||
            imageHeight <= 0
        ) {
            return;
        }

        const aspectRatio =
            selection.aspectRatio || 1;

        let newWidth = imageWidth;
        let newHeight =
            imageWidth / aspectRatio;

        if (newHeight > imageHeight) {
            newHeight = imageHeight;
            newWidth =
                imageHeight * aspectRatio;
        }

        const newX =
            imageX +
            (imageWidth - newWidth) / 2;

        const newY =
            imageY +
            (imageHeight - newHeight) / 2;

        if (
            !Number.isFinite(newX) ||
            !Number.isFinite(newY) ||
            newWidth <= 0 ||
            newHeight <= 0
        ) {
            return;
        }

        try {
            selection.$change(
                newX,
                newY,
                newWidth,
                newHeight,
            );

            this.schedulePreviewRender();
        } catch (error) {
            console.error(
                '[MediaDropzone] fit-to-width failed',
                {
                    name: this.name,
                    error,
                },
            );
        }
    }

    // =========================================================================
    // Fit selection to image height
    // =========================================================================

    fitSelectionToHeight() {
        const selection = this.cropperSelection;
        const image = this.cropperImage;

        if (!selection || !image) {
            return;
        }

        if (typeof image.$ready === 'function') {
            image.$ready()
                .then(() => {
                    this._fitSelectionToHeight(
                        selection,
                        image,
                    );
                })
                .catch(() => {
                    this._fitSelectionToHeight(
                        selection,
                        image,
                    );
                });

            return;
        }

        this._fitSelectionToHeight(
            selection,
            image,
        );
    }

    _fitSelectionToHeight(selection, image) {
        const container = this.cropperContainer;

        if (!container) {
            return;
        }

        const containerRect =
            container.getBoundingClientRect();

        const imageRect =
            image.getBoundingClientRect();

        const imageX =
            imageRect.left -
            containerRect.left;

        const imageY =
            imageRect.top -
            containerRect.top;

        const imageWidth = imageRect.width;
        const imageHeight = imageRect.height;

        if (
            imageWidth <= 0 ||
            imageHeight <= 0
        ) {
            return;
        }

        const aspectRatio =
            selection.aspectRatio || 1;

        let newHeight = imageHeight;
        let newWidth =
            imageHeight * aspectRatio;

        if (newWidth > imageWidth) {
            newWidth = imageWidth;
            newHeight =
                imageWidth / aspectRatio;
        }

        const newX =
            imageX +
            (imageWidth - newWidth) / 2;

        const newY =
            imageY +
            (imageHeight - newHeight) / 2;

        if (
            !Number.isFinite(newX) ||
            !Number.isFinite(newY) ||
            newWidth <= 0 ||
            newHeight <= 0
        ) {
            return;
        }

        try {
            selection.$change(
                newX,
                newY,
                newWidth,
                newHeight,
            );

            this.schedulePreviewRender();
        } catch (error) {
            console.error(
                '[MediaDropzone] fit-to-height failed',
                {
                    name: this.name,
                    error,
                },
            );
        }
    }

    // =========================================================================
    // Apply crop
    // =========================================================================

    async applyCrop() {
        const selection = this.cropperSelection;

        if (!selection) {
            console.warn(
                '[MediaDropzone] crop selection is not available',
                {
                    name: this.name,
                },
            );

            return;
        }

        if (!this.file) {
            console.warn(
                '[MediaDropzone] crop file is not available',
                {
                    name: this.name,
                },
            );

            return;
        }

        if (
            !Number.isFinite(this.imageWidth) ||
            this.imageWidth <= 0 ||
            !Number.isFinite(this.imageHeight) ||
            this.imageHeight <= 0
        ) {
            this.setError(
                'Некорректные настройки размера выходного изображения.',
            );

            return;
        }

        if (
            !Number.isFinite(this.imageQuality) ||
            this.imageQuality < 0 ||
            this.imageQuality > 1
        ) {
            this.setError(
                'Некорректные настройки качества выходного изображения.',
            );

            return;
        }

        const originalFile = this.file;

        try {
            this.setState('processing');

            if (
                typeof selection.$toCanvas !==
                'function'
            ) {
                throw new Error(
                    'CropperSelection.$toCanvas() is unavailable.',
                );
            }

            console.debug(
                '[MediaDropzone] applying crop',
                {
                    name: this.name,
                    file: originalFile.name,
                    originalSize:
                        originalFile.size,
                    outputWidth:
                        this.imageWidth,
                    outputHeight:
                        this.imageHeight,
                    outputQuality:
                        this.imageQuality,
                },
            );

            // ---------------------------------------------------------------------
            // 1. Generate final canvas
            // ---------------------------------------------------------------------

            const canvas =
                await selection.$toCanvas({
                    width: this.imageWidth,
                    height: this.imageHeight,
                });

            // ---------------------------------------------------------------------
            // 2. Convert canvas to JPEG
            // ---------------------------------------------------------------------

            const blob =
                await this.canvasToBlob(
                    canvas,
                    'image/jpeg',
                    this.imageQuality,
                );

            // ---------------------------------------------------------------------
            // 3. Create final File
            // ---------------------------------------------------------------------

            const croppedFile =
                this.createCroppedFile(
                    originalFile,
                    blob,
                );

            // ---------------------------------------------------------------------
            // 4. Replace selected file
            // ---------------------------------------------------------------------

            this.file = croppedFile;

            this.setInputFile(croppedFile);

            // ---------------------------------------------------------------------
            // 5. Update final preview
            // ---------------------------------------------------------------------

            this.updateFileInfo(
                croppedFile,
            );

            this.showImagePreview(
                croppedFile,
            );

            this.clearError();

            this.setState('selected');

            console.debug(
                '[MediaDropzone] cropped file created',
                {
                    name: this.name,
                    originalFile:
                        originalFile.name,
                    originalSize:
                        originalFile.size,
                    croppedFile:
                        croppedFile.name,
                    croppedSize:
                        croppedFile.size,
                    type:
                        croppedFile.type,
                    width:
                        canvas.width,
                    height:
                        canvas.height,
                    quality:
                        this.imageQuality,
                },
            );

            // ---------------------------------------------------------------------
            // 6. Close modal
            // ---------------------------------------------------------------------

            this.cropperModal?.hide();
        } catch (error) {
            this.setState('error');

            console.error(
                '[MediaDropzone] crop failed',
                {
                    name: this.name,
                    file: originalFile.name,
                    error,
                },
            );

            this.setError(
                'Не удалось обработать изображение. Попробуйте выбрать его ещё раз.',
            );
        }
    }

    // =========================================================================
    // Canvas -> Blob
    // =========================================================================

    async canvasToBlob(
        canvas,
        type,
        quality,
    ) {
        return new Promise(
            (resolve, reject) => {
                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(
                                new Error(
                                    'Не удалось создать изображение из canvas.',
                                ),
                            );

                            return;
                        }

                        resolve(blob);
                    },
                    type,
                    quality,
                );
            },
        );
    }

    // =========================================================================
    // Cropped file
    // =========================================================================

    createCroppedFile(
        originalFile,
        blob,
    ) {
        const originalName =
            originalFile.name;

        const lastDotIndex =
            originalName.lastIndexOf('.');

        const baseName =
            lastDotIndex > 0
                ? originalName.substring(
                      0,
                      lastDotIndex,
                  )
                : originalName;

        return new File(
            [blob],
            `${baseName}-cropped.jpg`,
            {
                type: 'image/jpeg',
                lastModified: Date.now(),
            },
        );
    }

    // =========================================================================
    // Crop cancellation
    // =========================================================================

    cancelCrop() {
        console.debug(
            '[MediaDropzone] crop cancelled',
            {
                name: this.name,
                file: this.file?.name,
            },
        );

        /*
         * Пока Apply не был вызван, this.file остаётся оригинальным File.
         *
         * Поэтому Cancel ничего не меняет в input и просто закрывает modal.
         */
        this.cropperModal?.hide();
    }

    // =========================================================================
    // Input
    // =========================================================================

    setInputFile(file) {
        if (!this.input) {
            throw new Error(
                'Media input element is not available.',
            );
        }

        try {
            this.clearRemoveSignal();

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            const nativeFilesSetter = Object.getOwnPropertyDescriptor(
                HTMLInputElement.prototype,
                'files'
            )?.set;

            const nativeValueSetter = Object.getOwnPropertyDescriptor(
                HTMLInputElement.prototype,
                'value'
            )?.set;

            if (nativeValueSetter) {
                nativeValueSetter.call(this.input, '');
            } else {
                this.input.value = '';
            }

            if (nativeFilesSetter) {
                nativeFilesSetter.call(this.input, dataTransfer.files);
            } else {
                this.input.files = dataTransfer.files;
            }

            const tracker = this.input._valueTracker;
            if (tracker) {
                tracker.setValue('');
            }

            this._isProgrammaticSync = true;

            let changeEvent, inputEvent;
            try {
                changeEvent = new Event('change', { bubbles: true, cancelable: true });
                inputEvent = new Event('input', { bubbles: true, cancelable: true });
            } catch (e) {
                changeEvent = document.createEvent('Event');
                changeEvent.initEvent('change', true, true);
                inputEvent = document.createEvent('Event');
                inputEvent.initEvent('input', true, true);
            }

            this.input.dispatchEvent(inputEvent);
            this.input.dispatchEvent(changeEvent);

            console.debug(
                '[MediaDropzone] single cropped file updated and events dispatched',
                { name: this.name, file: file.name }
            );
        } catch (error) {
            console.error('[MediaDropzone] failed to update input file', { error });
            throw error;
        } finally {
            this._isProgrammaticSync = false;
        }
    }

    clearFileInput() {
        if (!this.input) return;

        try {
            const nativeValueSetter = Object.getOwnPropertyDescriptor(
                HTMLInputElement.prototype,
                'value'
            )?.set;

            if (nativeValueSetter) {
                nativeValueSetter.call(this.input, '');
            } else {
                this.input.value = '';
            }

            const tracker = this.input._valueTracker;
            if (tracker) {
                tracker.setValue('diff');
                tracker.setValue('');
            }

            let changeEvent, inputEvent;
            try {
                changeEvent = new Event('change', { bubbles: true });
                inputEvent = new Event('input', { bubbles: true });
            } catch (e) {
                changeEvent = document.createEvent('Event');
                changeEvent.initEvent('change', true, false);
                inputEvent = document.createEvent('Event');
                inputEvent.initEvent('input', true, false);
            }

            this.input.dispatchEvent(inputEvent);
            this.input.dispatchEvent(changeEvent);

        } catch (error) {
            console.error('[MediaDropzone] Native input clear error:', error);
            this.input.value = '';
        }
    }

    // =========================================================================
    // File information
    // =========================================================================

    updateFileInfo(file) {
        if (!this.fileInfo) {
            return;
        }

        if (this.fileName) {
            this.fileName.textContent =
                file.name;
        }

        if (this.fileSize) {
            this.fileSize.textContent =
                this.formatFileSize(
                    file.size,
                );
        }

        this.fileInfo.hidden = false;

        if (this.removeButton) {
            this.removeButton.hidden = false;
        }
    }

    // =========================================================================
    // Remove
    // =========================================================================

    removeFile() {
        console.debug('[MediaDropzone] removing file', {
            name: this.name,
            file: this.file?.name,
            isExisting: this.isExisting,
        });

        if (this.isExisting && this.existingUrl && this.element) {
            try {
                const escapedName = CSS.escape(this.name);
                const oldRemoveField = this.element.querySelector(
                    `input[name="${escapedName}_remove"]`,
                );

                if (!oldRemoveField) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `${this.name}_remove`;
                    hiddenInput.value = '1';

                    this.element.appendChild(hiddenInput);

                    const changeEvent = new Event('change', { bubbles: true });
                    hiddenInput.dispatchEvent(changeEvent);
                }
            } catch (e) {
                console.error('[MediaDropzone] Failed to append remove tracking token:', e);
            }
        }

        this.destroyCropper();
        this.clearCropperPreview();
        this.clearImagePreview();
        this.clearVideoPreview();
        this.clearError();

        if (this.fileInfo) this.fileInfo.hidden = true;
        if (this.removeButton) this.removeButton.hidden = true;

        this.file = null;
        this.isExisting = false;
        this.existingUrl = null;

        this.clearFileInput();

        this.setState('idle');
    }

    // =========================================================================
    // Image preview
    // =========================================================================

    showImagePreview(file) {
        if (
            !this.preview ||
            !this.imagePreview
        ) {
            return;
        }

        this.clearImagePreview();
        this.clearVideoPreview();

        this.previewObjectUrl =
            URL.createObjectURL(file);

        const image =
            document.createElement(
                'img',
            );

        image.src =
            this.previewObjectUrl;

        image.alt =
            file.name;

        image.classList.add(
            'media-dropzone__image',
        );

        this.imagePreview.replaceChildren(
            image,
        );

        this.imagePreview.hidden =
            false;

        this.preview.hidden =
            false;

        this.updateFileInfo(file);
    }

    clearImagePreview() {
        if (this.imagePreview) {
            this.imagePreview.replaceChildren();
            this.imagePreview.hidden =
                true;
        }

        if (this.previewObjectUrl) {
            URL.revokeObjectURL(
                this.previewObjectUrl,
            );

            this.previewObjectUrl =
                null;
        }

        this.updatePreviewVisibility();
    }

    // =========================================================================
    // Video preview
    // =========================================================================

    showVideoPreview(file) {
        if (
            !this.preview ||
            !this.videoPreview
        ) {
            return;
        }

        this.clearImagePreview();
        this.clearVideoPreview();

        this.videoPreviewObjectUrl =
            URL.createObjectURL(file);

        const video =
            document.createElement(
                'video',
            );

        video.src =
            this.videoPreviewObjectUrl;

        video.controls = true;
        video.preload = 'metadata';

        video.classList.add(
            'media-dropzone__video',
        );

        this.videoPreview.replaceChildren(
            video,
        );

        this.videoPreview.hidden =
            false;

        this.preview.hidden =
            false;

        this.updateFileInfo(file);
    }

    clearVideoPreview() {
        if (this.videoPreview) {
            const video =
                this.videoPreview.querySelector(
                    'video',
                );

            if (video) {
                video.pause();
                video.removeAttribute(
                    'src',
                );
                video.load();
            }

            this.videoPreview.replaceChildren();

            this.videoPreview.hidden =
                true;
        }

        if (
            this.videoPreviewObjectUrl
        ) {
            URL.revokeObjectURL(
                this.videoPreviewObjectUrl,
            );

            this.videoPreviewObjectUrl =
                null;
        }

        this.updatePreviewVisibility();
    }

    updatePreviewVisibility() {
        if (!this.preview) {
            return;
        }

        const hasImage =
            this.imagePreview &&
            !this.imagePreview.hidden;

        const hasVideo =
            this.videoPreview &&
            !this.videoPreview.hidden;

        this.preview.hidden =
            !hasImage &&
            !hasVideo;
    }

    // =========================================================================
    // Cropper lifecycle
    // =========================================================================

    destroyCropper() {
        this.isDestroying = true;

        // -------------------------------------------------------------------------
        // Cancel preview rendering
        // -------------------------------------------------------------------------

        if (
            this.previewFrameRequest !==
            null
        ) {
            cancelAnimationFrame(
                this.previewFrameRequest,
            );

            this.previewFrameRequest =
                null;
        }

        this.previewRenderSequence++;

        // -------------------------------------------------------------------------
        // Remove native elements
        // -------------------------------------------------------------------------

        if (this.cropperContainer) {
            this.cropperContainer.replaceChildren();
        }

        // -------------------------------------------------------------------------
        // Clear references
        // -------------------------------------------------------------------------

        this.cropper = null;
        this.cropperCanvas = null;
        this.cropperImage = null;
        this.cropperSelection = null;

        // -------------------------------------------------------------------------
        // Object URL
        // -------------------------------------------------------------------------

        this.revokeCropperObjectUrl();

        this.isDestroying = false;
    }

    revokeCropperObjectUrl() {
        if (this.objectUrl) {
            URL.revokeObjectURL(
                this.objectUrl,
            );

            this.objectUrl = null;
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    parseRatio(ratio) {
        const match =
            /^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)$/.exec(
                ratio ?? '',
            );

        if (!match) {
            console.warn(
                '[MediaDropzone] invalid aspect ratio',
                {
                    name: this.name,
                    ratio,
                },
            );

            return null;
        }

        const width =
            Number(match[1]);

        const height =
            Number(match[2]);

        if (
            !Number.isFinite(width) ||
            !Number.isFinite(height) ||
            width <= 0 ||
            height <= 0
        ) {
            return null;
        }

        return width / height;
    }

    parsePositiveNumber(value) {
        const number =
            Number(value);

        return Number.isFinite(number) &&
            number > 0
            ? number
            : null;
    }

    formatFileSize(bytes) {
        if (
            bytes <
            1024 * 1024
        ) {
            return `${Math.round(
                bytes / 1024,
            )} КБ`;
        }

        return `${(
            bytes /
            (1024 * 1024)
        ).toFixed(1)} МБ`;
    }

    // =========================================================================
    // Error handling
    // =========================================================================

    setError(message) {
        this.setState('error');

        if (!this.message) {
            return;
        }

        this.message.innerHTML = `
            <span class="media-dropzone__message-text">
                ${message}
            </span>

            <button
                type="button"
                class="media-dropzone__retry-btn"
                data-retry
            >
                <i
                    class="bi bi-arrow-clockwise"
                    aria-hidden="true"
                ></i>

                Попробовать снова
            </button>
        `;

        this.message.hidden = false;

        if (this.surface) {
            this.surface.classList.add(
                'is-error',
            );
        }

        const retryButton =
            this.message.querySelector(
                '[data-retry]',
            );

        retryButton?.addEventListener(
            'click',
            (event) => {
                event.stopPropagation();

                this.clearFileInput();
                this.clearError();
            },
        );
    }

    clearError() {
        if (!this.message) {
            return;
        }

        this.message.innerHTML = '';
        this.message.hidden = true;

        if (this.surface) {
            this.surface.classList.remove(
                'is-error',
            );
        }
    }

    // =========================================================================
    // State
    // =========================================================================

    setState(state) {
        this.state = state;
    }
}

// =============================================================================
// Initialization
// =============================================================================

window.initMediaDropzones = function (
    container = document,
) {
    container
        .querySelectorAll(
            '[data-media-dropzone]',
        )
        .forEach((element) => {
            if (
                !element.dataset
                    .mediaDropzoneInitialized
            ) {
                element.dataset
                    .mediaDropzoneInitialized =
                    'true';

                new MediaDropzone(
                    element,
                );
            }
        });
};

document
    .querySelectorAll(
        '[data-media-dropzone]',
    )
    .forEach((element) => {
        if (
            !element.dataset
                .mediaDropzoneInitialized
        ) {
            element.dataset
                .mediaDropzoneInitialized =
                'true';

            new MediaDropzone(element);
        }
    });
