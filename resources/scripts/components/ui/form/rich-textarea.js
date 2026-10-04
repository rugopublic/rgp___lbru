/**
 * UI RichTextarea
 *
 * Features:
 * - contenteditable editor;
 * - synchronization with the real textarea form field;
 * - native input event emission;
 * - toolbar command handling;
 * - inline link creation and editing;
 * - inline link removal;
 * - Bootstrap alert insertion;
 * - JPEG image insertion from the local computer;
 * - local image previews via Object URLs;
 * - image selection without native browser selection;
 * - dedicated image selection frame;
 * - four-corner proportional image resizing;
 * - keyboard image removal;
 * - Backspace image removal from the position immediately after an image;
 * - seamless alert removal with Backspace / Delete;
 * - pending File tracking;
 * - temporary file token management;
 * - upload token -> upload_id resolution;
 * - cleanup of pending files removed before submission.
 *
 * The RichTextarea does not perform uploads itself.
 * FormSender is responsible for uploading pending Files and resolving
 * their temporary tokens into server-generated upload IDs.
 */

const initializedRichTextareas = new WeakSet();
const richTextareaInstances = new WeakMap();

/**
 * Minimum image width in pixels.
 *
 * @type {number}
 */
const MIN_IMAGE_WIDTH = 80;

/**
 * CSS class permanently assigned to images managed by RichTextarea.
 *
 * @type {string}
 */
const IMAGE_CLASS = 'rta-image';

/**
 * CSS class used internally to identify editable alert blocks.
 *
 * The class is removed when the editor HTML is serialized.
 *
 * @type {string}
 */
const ALERT_EDITOR_CLASS = 'rta-alert';

/**
 * Supported Bootstrap alert variants.
 *
 * @type {Object<string, string>}
 */
const ALERT_TYPES = Object.freeze({
    info: 'alert-info',
    success: 'alert-success',
    warning: 'alert-warning',
    danger: 'alert-danger',
});

/**
 * Default text inserted into a new alert.
 *
 * @type {string}
 */
const DEFAULT_ALERT_TEXT = 'Текст уведомления';

/**
 * CSS class used to mark a link currently managed by the RTA link editor.
 *
 * @type {string}
 */
const LINK_SELECTED_CLASS = 'rta-link-selected';

/**
 * Generates a unique temporary identifier for a local image file.
 *
 * @returns {string}
 */
function generateFileToken() {
    if (
        typeof crypto !== 'undefined'
        && typeof crypto.randomUUID === 'function'
    ) {
        return `rta-${crypto.randomUUID()}`;
    }

    return `rta-${Date.now()}-${Math.random().toString(36).slice(2)}`;
}

/**
 * Determines whether a file is a supported JPEG image.
 *
 * The MIME type is preferred, while the filename extension is used
 * as a fallback for browsers that provide an incomplete MIME type.
 *
 * @param {File} file
 *
 * @returns {boolean}
 */
function isJpegFile(file) {
    if (!(file instanceof File)) {
        return false;
    }

    if (file.type === 'image/jpeg') {
        return true;
    }

    return /\.(jpe?g)$/i.test(file.name);
}

/**
 * Determines whether an alert type is supported.
 *
 * @param {string} type
 *
 * @returns {boolean}
 */
function isAlertType(type) {
    return Object.prototype.hasOwnProperty.call(
        ALERT_TYPES,
        type
    );
}

/**
 * Removes the browser's current native selection.
 *
 * @returns {void}
 */
function clearBrowserSelection() {
    const selection = window.getSelection();

    if (!selection) {
        return;
    }

    selection.removeAllRanges();
}

/**
 * RichTextarea controller.
 */
class RichTextarea {
    /**
     * @param {HTMLTextAreaElement} textarea
     */
    constructor(textarea) {
        if (!(textarea instanceof HTMLTextAreaElement)) {
            throw new TypeError(
                "The 'textarea' must be a valid textarea element."
            );
        }

        const root = textarea.closest('.rich-editor');

        if (!(root instanceof HTMLElement)) {
            throw new TypeError(
                'RichTextarea root element was not found.'
            );
        }

        const editor = root.querySelector('.editor-content');

        if (!(editor instanceof HTMLElement)) {
            throw new TypeError(
                'RichTextarea editor element was not found.'
            );
        }

        this.textarea = textarea;
        this.root = root;
        this.editor = editor;
        this.toolbar = root.querySelector('.editor-toolbar');

        /**
         * Pending local files indexed by temporary token.
         *
         * @type {Map<string, {
         *     file: File,
         *     objectUrl: string
         * }>}
         */
        this.pendingFiles = new Map();

        /**
         * Object URLs created by this instance.
         *
         * @type {Set<string>}
         */
        this.objectUrls = new Set();

        /**
         * Currently selected image.
         *
         * @type {HTMLImageElement|null}
         */
        this.selectedImage = null;

        /**
         * Current proportional resize operation.
         *
         * @type {{
         *     image: HTMLImageElement,
         *     pointerId: number,
         *     handle: string,
         *     startX: number,
         *     startY: number,
         *     startWidth: number,
         *     ratio: number
         * }|null}
         */
        this.resizeState = null;

        /**
         * Saved browser selection inside the editor.
         *
         * This is the single source of truth for selections that must
         * survive focus changes to the toolbar or link popover.
         *
         * @type {Range|null}
         */
        this.savedRange = null;

        /**
         * Hidden native file picker used by the image toolbar button.
         *
         * @type {HTMLInputElement|null}
         */
        this.imageInput = null;

        /**
         * Visual image selection frame.
         *
         * @type {HTMLDivElement|null}
         */
        this.selectionFrame = null;

        /**
         * Currently selected link.
         *
         * @type {HTMLAnchorElement|null}
         */
        this.selectedLink = null;

        /**
         * Active link editing popover.
         *
         * This is a DOM element owned by RichTextarea and is intentionally
         * outside `.editor-content`, so it is never serialized into article
         * HTML.
         *
         * @type {HTMLDivElement|null}
         */
        this.linkPopover = null;

        /**
         * Current link popover mode.
         *
         * @type {'edit'|'create'|null}
         */
        this.linkPopoverMode = null;

        /**
         * Selection saved for creating a new link.
         *
         * @type {Range|null}
         */
        this.linkCreationRange = null;

        this.boundSync = this.sync.bind(this);

        this.boundEditorInput =
            this.handleEditorInput.bind(this);

        this.boundToolbarMouseDown =
            this.handleToolbarMouseDown.bind(this);

        this.boundToolbarClick =
            this.handleToolbarClick.bind(this);

        this.boundImageInputChange =
            this.handleImageInputChange.bind(this);

        this.boundEditorPointerDown =
            this.handleEditorPointerDown.bind(this);

        this.boundEditorClick =
            this.handleEditorClick.bind(this);

        this.boundEditorDragStart =
            this.handleEditorDragStart.bind(this);

        this.boundEditorKeyDown =
            this.handleEditorKeyDown.bind(this);

        this.boundEditorKeyUp =
            this.saveSelection.bind(this);

        this.boundEditorMouseUp =
            this.saveSelection.bind(this);

        this.boundSelectionChange =
            this.handleSelectionChange.bind(this);

        this.boundResizePointerDown =
            this.handleResizePointerDown.bind(this);

        this.boundResizePointerMove =
            this.handleResizePointerMove.bind(this);

        this.boundResizePointerUp =
            this.handleResizePointerUp.bind(this);

        this.boundDocumentPointerDown =
            this.handleDocumentPointerDown.bind(this);

        this.boundWindowResize =
            this.handleWindowResize.bind(this);

        this.boundWindowScroll =
            this.handleWindowScroll.bind(this);

        this.boundLinkPopoverClick =
            this.handleLinkPopoverClick.bind(this);

        this.boundLinkPopoverKeyDown =
            this.handleLinkPopoverKeyDown.bind(this);

        this.initialize();
    }

    /**
     * Saves the current editor selection range.
     *
     * This remains as a compatibility entry point for the editor's
     * selection model. The actual state is stored only in `savedRange`.
     *
     * @returns {void}
     */
    saveEditorSelection() {
        this.saveSelection();
    }

    /**
     * Initializes the RichTextarea instance.
     *
     * @returns {void}
     */
    initialize() {
        this.createImageInput();
        this.createSelectionFrame();
        this.bindEditor();
        this.bindToolbar();
        this.prepareExistingImages();
        this.prepareExistingAlerts();

        /*
         * Synchronize the real form field with the existing editor contents
         * without emitting an input event during initialization.
         */
        this.textarea.value = this.serializeEditorHtml();

        this.initialized = true;
    }

    /**
     * Creates the hidden browser file input used to select JPEG images.
     *
     * @returns {void}
     */
    createImageInput() {
        const input = document.createElement('input');

        input.type = 'file';
        input.accept = 'image/jpeg,.jpg,.jpeg';
        input.className = 'd-none';
        input.tabIndex = -1;

        input.setAttribute(
            'data-rich-textarea-image-input',
            ''
        );

        input.setAttribute(
            'aria-hidden',
            'true'
        );

        input.addEventListener(
            'change',
            this.boundImageInputChange
        );

        this.root.append(input);

        this.imageInput = input;
    }

    /**
     * Creates the visual image selection frame with four resize anchors.
     *
     * @returns {void}
     */
    createSelectionFrame() {
        const frame = document.createElement('div');

        frame.className = 'rta-image-resizer';
        frame.hidden = true;

        frame.setAttribute(
            'aria-hidden',
            'true'
        );

        const handles = [
            'nw',
            'ne',
            'sw',
            'se',
        ];

        handles.forEach((position) => {
            const handle = document.createElement('span');

            handle.className =
                'rta-image-resizer__handle';

            handle.dataset.handle = position;

            handle.setAttribute(
                'aria-hidden',
                'true'
            );

            handle.addEventListener(
                'pointerdown',
                this.boundResizePointerDown
            );

            frame.append(handle);
        });

        this.root.append(frame);

        this.selectionFrame = frame;

        const computedStyle =
            window.getComputedStyle(this.root);

        if (computedStyle.position === 'static') {
            this.root.style.position = 'relative';
        }
    }

    /**
     * Binds editor events.
     *
     * @returns {void}
     */
    bindEditor() {
        this.editor.addEventListener(
            'input',
            this.boundEditorInput
        );

        this.editor.addEventListener(
            'blur',
            this.boundSync
        );

        this.editor.addEventListener(
            'pointerdown',
            this.boundEditorPointerDown
        );

        this.editor.addEventListener(
            'click',
            this.boundEditorClick
        );

        this.editor.addEventListener(
            'dragstart',
            this.boundEditorDragStart
        );

        this.editor.addEventListener(
            'keydown',
            this.boundEditorKeyDown
        );

        this.editor.addEventListener(
            'keyup',
            this.boundEditorKeyUp
        );

        this.editor.addEventListener(
            'mouseup',
            this.boundEditorMouseUp
        );

        document.addEventListener(
            'selectionchange',
            this.boundSelectionChange
        );

        document.addEventListener(
            'pointerdown',
            this.boundDocumentPointerDown
        );

        window.addEventListener(
            'resize',
            this.boundWindowResize
        );

        window.addEventListener(
            'scroll',
            this.boundWindowScroll,
            true
        );
    }

    /**
     * Binds toolbar events using delegated handlers.
     *
     * @returns {void}
     */
    bindToolbar() {
        if (!(this.toolbar instanceof HTMLElement)) {
            return;
        }

        this.toolbar.addEventListener(
            'mousedown',
            this.boundToolbarMouseDown
        );

        this.toolbar.addEventListener(
            'click',
            this.boundToolbarClick
        );
    }

    /**
     * Handles editor input.
     *
     * @returns {void}
     */
    handleEditorInput() {
        this.sync();
    }

    /**
     * Prevents toolbar controls from stealing the current editor selection
     * before their action is processed.
     *
     * @param {MouseEvent} event
     *
     * @returns {void}
     */
    handleToolbarMouseDown(event) {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const button =
            target.closest('button');

        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        const isAlertButton =
            button.dataset.rtaAlertType !== undefined;

        const isCommandButton =
            button.dataset.command !== undefined;

        /*
         * The dropdown toggle itself does not carry either marker and is
         * therefore intentionally excluded.
         */
        if (
            !isAlertButton
            && !isCommandButton
        ) {
            return;
        }

        /*
         * Always capture the selection before the toolbar is allowed
         * to move focus.
         */
        this.saveSelection();

        /*
         * Preventing the default mousedown keeps the editor selection
         * visually alive and prevents toolbar focus from replacing it.
         */
        event.preventDefault();
    }

    /**
     * Executes a toolbar action.
     *
     * @param {MouseEvent} event
     *
     * @returns {void}
     */
    handleToolbarClick(event) {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const alertButton =
            target.closest(
                'button[data-rta-alert-type]'
            );

        if (alertButton instanceof HTMLButtonElement) {
            event.preventDefault();

            this.insertAlert(
                alertButton.dataset.rtaAlertType
            );

            return;
        }

        const commandButton =
            target.closest(
                'button[data-command]'
            );

        if (!(commandButton instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();

        const command =
            commandButton.dataset.command;

        if (!command) {
            return;
        }

        if (command === 'insertImage') {
            this.openImagePicker();

            return;
        }

        const value =
            commandButton.dataset.value || null;

        this.executeCommand(
            command,
            value
        );
    }

    /**
     * Opens the native JPEG file picker.
     *
     * @returns {void}
     */
    openImagePicker() {
        if (!(this.imageInput instanceof HTMLInputElement)) {
            return;
        }

        this.imageInput.value = '';

        this.imageInput.click();
    }

    /**
     * Handles local JPEG file selection.
     *
     * @param {Event} event
     *
     * @returns {void}
     */
    handleImageInputChange(event) {
        const input = event.target;

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const file = input.files?.[0] ?? null;

        if (!file) {
            return;
        }

        if (!isJpegFile(file)) {
            window.alert(
                'Можно вставлять только изображения JPG / JPEG.'
            );

            input.value = '';

            return;
        }

        this.insertImageFile(file);

        input.value = '';
    }

    /**
     * Saves the current browser selection when it belongs to the editor.
     *
     * @returns {void}
     */
    saveSelection() {
        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
        ) {
            return;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return;
        }

        this.savedRange =
            range.cloneRange();
    }

    /**
     * Restores the previously saved editor selection.
     *
     * @returns {void}
     */
    restoreSelection() {
        this.editor.focus();

        if (!(this.savedRange instanceof Range)) {
            return;
        }

        const selection =
            window.getSelection();

        if (!selection) {
            return;
        }

        try {
            selection.removeAllRanges();

            selection.addRange(
                this.savedRange.cloneRange()
            );
        } catch {
            return;
        }
    }

    /**
     * Executes a formatting command.
     *
     * Link commands use the inline link editor instead of prompt().
     *
     * @param {string} command
     * @param {string|null} value
     *
     * @returns {void}
     */
    executeCommand(
        command,
        value = null
    ) {
        if (command === 'createLink') {
            const range =
                this.savedRange instanceof Range
                    ? this.savedRange.cloneRange()
                    : null;

            if (
                !(range instanceof Range)
                || range.collapsed
                || !this.editor.contains(
                    range.commonAncestorContainer
                )
            ) {
                return;
            }

            const existingLink =
                this.getLinkContainingRange(
                    range
                );

            if (existingLink) {
                this.showLinkPopover(
                    existingLink,
                    true
                );

                return;
            }

            this.showCreateLinkPopover(
                range
            );

            return;
        }

        if (command === 'unlink') {
            const existingLink =
                this.selectedLink
                ?? this.getLinkContainingRange(
                    this.savedRange
                );

            if (existingLink) {
                this.removeSelectedLink(
                    existingLink
                );

                return;
            }

            this.hideLinkPopover();

            this.restoreSelection();

            document.execCommand(
                command,
                false,
                value
            );

            this.sync();

            return;
        }

        this.hideLinkPopover();
        this.restoreSelection();

        document.execCommand(
            command,
            false,
            value
        );

        this.sync();
    }

    /**
     * Creates a new Bootstrap alert block.
     *
     * @param {string} type
     *
     * @returns {HTMLDivElement}
     */
    createAlert(type) {
        const alert =
            document.createElement('div');

        alert.classList.add(
            'alert',
            ALERT_TYPES[type],
            ALERT_EDITOR_CLASS
        );

        alert.dataset.rtaAlert = type;

        alert.setAttribute(
            'role',
            'alert'
        );

        alert.textContent =
            DEFAULT_ALERT_TEXT;

        return alert;
    }

    /**
     * Finds an alert containing the given selection range.
     *
     * @param {Range|null} range
     *
     * @returns {HTMLDivElement|null}
     */
    getAlertContainingRange(range) {
        if (!(range instanceof Range)) {
            return null;
        }

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return null;
        }

        const startElement =
            range.startContainer.nodeType === Node.ELEMENT_NODE
                ? range.startContainer
                : range.startContainer.parentElement;

        if (!(startElement instanceof Element)) {
            return null;
        }

        const startAlert =
            startElement.closest(
                `.${ALERT_EDITOR_CLASS}`
            );

        if (!(startAlert instanceof HTMLDivElement)) {
            return null;
        }

        const endElement =
            range.endContainer.nodeType === Node.ELEMENT_NODE
                ? range.endContainer
                : range.endContainer.parentElement;

        if (!(endElement instanceof Element)) {
            return null;
        }

        const endAlert =
            endElement.closest(
                `.${ALERT_EDITOR_CLASS}`
            );

        return endAlert === startAlert
            ? startAlert
            : null;
    }

    /**
     * Inserts a Bootstrap alert into the editor.
     *
     * Alerts are structural top-level blocks.
     *
     * @param {string} type
     *
     * @returns {HTMLDivElement|null}
     */
    insertAlert(type) {
        if (!isAlertType(type)) {
            return null;
        }

        const alert =
            this.createAlert(type);

        this.restoreSelection();

        const containingAlert =
            this.getAlertContainingRange(
                this.savedRange
            );

        if (containingAlert) {
            this.insertAlertAfterNode(
                containingAlert,
                alert
            );

            this.selectAlertText(
                alert
            );

            this.sync();

            return alert;
        }

        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
        ) {
            this.editor.append(alert);

            this.insertTrailingParagraph(
                alert
            );

            this.selectAlertText(
                alert
            );

            this.sync();

            return alert;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            this.editor.append(alert);

            this.insertTrailingParagraph(
                alert
            );

            this.selectAlertText(
                alert
            );

            this.sync();

            return alert;
        }

        range.deleteContents();

        const directChild =
            this.getDirectEditorChild(
                range.startContainer
            );

        if (!directChild) {
            this.editor.append(alert);

            this.insertTrailingParagraph(
                alert
            );
        } else if (
            directChild instanceof HTMLImageElement
        ) {
            this.insertAlertAfterNode(
                directChild,
                alert
            );
        } else if (
            this.isSplittableBlock(
                directChild
            )
        ) {
            this.splitBlockAndInsertAlert(
                directChild,
                range,
                alert
            );
        } else {
            this.insertAlertAfterNode(
                directChild,
                alert
            );
        }

        this.selectAlertText(
            alert
        );

        this.sync();

        return alert;
    }

    /**
     * Determines whether a top-level editor node can safely be split around
     * the current caret position.
     *
     * @param {Node} node
     *
     * @returns {boolean}
     */
    isSplittableBlock(node) {
        if (!(node instanceof HTMLElement)) {
            return false;
        }

        return [
            'P',
            'H1',
            'H2',
            'H3',
            'H4',
            'H5',
            'H6',
            'BLOCKQUOTE',
            'PRE',
            'DIV',
        ].includes(node.nodeName);
    }

    /**
     * Finds the direct child of the editor that contains a given node.
     *
     * @param {Node} node
     *
     * @returns {HTMLElement|null}
     */
    getDirectEditorChild(node) {
        let current =
            node instanceof Element
                ? node
                : node.parentElement;

        while (
            current
            && current.parentElement !== this.editor
        ) {
            current = current.parentElement;
        }

        return (
            current instanceof HTMLElement
                ? current
                : null
        );
    }

    /**
     * Splits a top-level block at the current selection and inserts an alert
     * between the two resulting blocks.
     *
     * @param {HTMLElement} block
     * @param {Range} range
     * @param {HTMLDivElement} alert
     *
     * @returns {void}
     */
    splitBlockAndInsertAlert(
        block,
        range,
        alert
    ) {
        const parent = block.parentNode;

        if (!parent) {
            return;
        }

        const trailingRange =
            document.createRange();

        trailingRange.selectNodeContents(
            block
        );

        trailingRange.setStart(
            range.startContainer,
            range.startOffset
        );

        const trailingContents =
            trailingRange.extractContents();

        if (!block.hasChildNodes()) {
            block.append(
                document.createElement('br')
            );
        }

        parent.insertBefore(
            alert,
            block.nextSibling
        );

        if (trailingContents.hasChildNodes()) {
            const trailingBlock =
                block.cloneNode(false);

            trailingBlock.append(
                trailingContents
            );

            parent.insertBefore(
                trailingBlock,
                alert.nextSibling
            );
        } else {
            this.insertTrailingParagraph(
                alert,
                alert.nextSibling
            );
        }
    }

    /**
     * Inserts an alert immediately after an existing editor node.
     *
     * @param {Node} node
     * @param {HTMLDivElement} alert
     *
     * @returns {void}
     */
    insertAlertAfterNode(
        node,
        alert
    ) {
        const parent =
            node.parentNode;

        if (!parent) {
            return;
        }

        let referenceNode =
            node.nextSibling;

        if (
            referenceNode
            && referenceNode.nodeType === Node.ELEMENT_NODE
            && referenceNode.nodeName === 'BR'
        ) {
            parent.insertBefore(
                alert,
                referenceNode.nextSibling
            );

            this.insertTrailingParagraph(
                alert,
                alert.nextSibling
            );

            return;
        }

        parent.insertBefore(
            alert,
            referenceNode
        );

        this.insertTrailingParagraph(
            alert,
            alert.nextSibling
        );
    }

    /**
     * Inserts an empty paragraph after an alert when necessary.
     *
     * @param {HTMLDivElement} alert
     * @param {Node|null} referenceNode
     *
     * @returns {void}
     */
    insertTrailingParagraph(
        alert,
        referenceNode = null
    ) {
        if (
            alert.nextSibling
            && alert.nextSibling.nodeType === Node.ELEMENT_NODE
            && (
                alert.nextSibling.nodeName === 'P'
                || alert.nextSibling.nodeName === 'DIV'
            )
        ) {
            return;
        }

        const paragraph =
            document.createElement('p');

        paragraph.append(
            document.createElement('br')
        );

        alert.parentNode?.insertBefore(
            paragraph,
            referenceNode ?? alert.nextSibling
        );
    }

    /**
     * Selects all text inside an alert so its placeholder text can
     * immediately be replaced by typing.
     *
     * @param {HTMLDivElement} alert
     *
     * @returns {void}
     */
    selectAlertText(alert) {
        const range =
            document.createRange();

        range.selectNodeContents(
            alert
        );

        const selection =
            window.getSelection();

        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(range);

        this.savedRange =
            range.cloneRange();
    }

    /**
     * Creates a local JPEG image and registers its File as pending upload
     * state.
     *
     * @param {File} file
     *
     * @returns {HTMLImageElement|null}
     */
    insertImageFile(file) {
        if (!isJpegFile(file)) {
            return null;
        }

        const token =
            generateFileToken();

        const objectUrl =
            URL.createObjectURL(file);

        this.objectUrls.add(
            objectUrl
        );

        this.pendingFiles.set(
            token,
            {
                file,
                objectUrl,
            }
        );

        const image =
            document.createElement('img');

        image.classList.add(
            IMAGE_CLASS
        );

        image.src = objectUrl;
        image.alt = file.name;
        image.draggable = false;

        image.dataset.rtaFileToken =
            token;

        image.addEventListener(
            'load',
            () => {
                this.limitImageToEditorWidth(
                    image
                );

                this.positionSelectionFrame();
            },
            { once: true }
        );

        this.insertImageAtSelection(
            image
        );

        this.selectImage(
            image
        );

        this.sync();

        return image;
    }

    /**
     * Inserts an image at the current editor selection.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    insertImageAtSelection(image) {
        this.restoreSelection();

        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
        ) {
            this.editor.append(image);

            this.insertTrailingBreak(image);

            this.placeCaretAfterImage(image);

            return;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            this.editor.append(image);

            this.insertTrailingBreak(image);

            this.placeCaretAfterImage(image);

            return;
        }

        range.deleteContents();

        range.insertNode(image);

        this.insertTrailingBreak(
            image
        );

        this.placeCaretAfterImage(
            image
        );
    }

    /**
     * Ensures that an inserted image is followed by a line break.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    insertTrailingBreak(image) {
        const nextSibling =
            image.nextSibling;

        if (
            nextSibling
            && nextSibling.nodeType === Node.ELEMENT_NODE
            && nextSibling.nodeName === 'BR'
        ) {
            return;
        }

        const breakNode =
            document.createElement('br');

        image.parentNode?.insertBefore(
            breakNode,
            image.nextSibling
        );
    }

    /**
     * Places the caret immediately after an image.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    placeCaretAfterImage(image) {
        const range =
            document.createRange();

        range.setStartAfter(
            image
        );

        range.collapse(true);

        const selection =
            window.getSelection();

        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(range);

        this.savedRange =
            range.cloneRange();
    }

    /**
     * Handles pointer interaction inside the editor.
     *
     * Clicking an existing link opens its inline editor instead of following
     * the URL.
     *
     * @param {PointerEvent} event
     *
     * @returns {void}
     */
    handleEditorPointerDown(event) {
        const target =
            event.target;

        const link =
            this.getLinkFromTarget(
                target
            );

        if (link) {
            event.preventDefault();
            event.stopPropagation();

            this.selectLink(
                link,
                true
            );

            return;
        }

        if (
            target instanceof HTMLImageElement
            && this.editor.contains(target)
        ) {
            event.preventDefault();
            event.stopPropagation();

            this.hideLinkPopover();
            this.selectImage(target);

            return;
        }

        this.hideLinkPopover();

        if (
            this.selectedImage
            && !this.isSelectionFrameTarget(target)
        ) {
            this.clearImageSelection();
        }
    }

    /**
     * Handles clicks inside the editor.
     *
     * Clicking an existing link opens its inline editor instead of following
     * the URL.
     *
     * @param {MouseEvent} event
     *
     * @returns {void}
     */
    handleEditorClick(event) {
        const target =
            event.target;

        const link =
            this.getLinkFromTarget(
                target
            );

        if (link) {
            event.preventDefault();
            event.stopPropagation();

            this.selectLink(
                link,
                true
            );

            return;
        }

        if (
            target instanceof HTMLImageElement
            && this.editor.contains(target)
        ) {
            event.preventDefault();
            event.stopPropagation();

            this.hideLinkPopover();
            this.selectImage(target);

            return;
        }

        this.hideLinkPopover();

        if (
            this.selectedImage
            && !this.isSelectionFrameTarget(target)
        ) {
            this.clearImageSelection();
        }
    }

    /**
     * Determines whether an event target belongs to the image selection
     * frame.
     *
     * @param {EventTarget|null} target
     *
     * @returns {boolean}
     */
    isSelectionFrameTarget(target) {
        return (
            target instanceof Element
            && this.selectionFrame instanceof HTMLElement
            && this.selectionFrame.contains(target)
        );
    }

    /**
     * Prevents native browser dragging of editor images.
     *
     * @param {DragEvent} event
     *
     * @returns {void}
     */
    handleEditorDragStart(event) {
        if (
            event.target instanceof HTMLImageElement
            && this.editor.contains(event.target)
        ) {
            event.preventDefault();
        }
    }

    /**
     * Finds the RTA image immediately preceding the current collapsed caret.
     *
     * @returns {HTMLImageElement|null}
     */
    getImageImmediatelyBeforeCaret() {
        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
            || !selection.isCollapsed
        ) {
            return null;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return null;
        }

        let container =
            range.startContainer;

        let offset =
            range.startOffset;

        if (
            container.nodeType === Node.TEXT_NODE
        ) {
            if (offset > 0) {
                return null;
            }

            const parent =
                container.parentNode;

            if (!parent) {
                return null;
            }

            offset =
                Array.prototype.indexOf.call(
                    parent.childNodes,
                    container
                );

            container = parent;
        }

        if (!(container instanceof Node)) {
            return null;
        }

        let previousNode =
            container.childNodes[offset - 1]
            ?? null;

        if (
            previousNode
            && previousNode.nodeType === Node.ELEMENT_NODE
            && previousNode.nodeName === 'BR'
        ) {
            previousNode =
                previousNode.previousSibling;
        }

        while (
            previousNode
            && previousNode.nodeType === Node.TEXT_NODE
            && !(previousNode.textContent ?? '').length
        ) {
            previousNode =
                previousNode.previousSibling;
        }

        if (
            previousNode instanceof HTMLImageElement
            && previousNode.classList.contains(
                IMAGE_CLASS
            )
            && this.editor.contains(previousNode)
        ) {
            return previousNode;
        }

        return null;
    }

    /**
     * Handles keyboard interaction with images, alerts and links.
     *
     * @param {KeyboardEvent} event
     *
     * @returns {void}
     */
    handleEditorKeyDown(event) {
        if (
            event.key === 'Escape'
            && this.selectedImage
        ) {
            event.preventDefault();

            this.clearImageSelection();

            return;
        }

        if (
            (
                event.key === 'Delete'
                || event.key === 'Backspace'
            )
            && this.selectedImage
        ) {
            event.preventDefault();

            this.removeImage(
                this.selectedImage
            );

            return;
        }

        if (
            event.key === 'Escape'
            && this.linkPopover
        ) {
            event.preventDefault();

            this.hideLinkPopover();

            return;
        }

        const alert =
            this.getContainingAlert();

        if (
            alert
            && this.handleAlertBoundaryKey(
                event,
                alert
            )
        ) {
            return;
        }

        if (event.key === 'Backspace') {
            const previousAlert =
                this.getAlertImmediatelyBeforeCaret();

            if (previousAlert) {
                event.preventDefault();

                this.removeAlert(
                    previousAlert
                );

                return;
            }
        }

        if (event.key === 'Delete') {
            const nextAlert =
                this.getAlertImmediatelyAfterCaret();

            if (nextAlert) {
                event.preventDefault();

                this.removeAlert(
                    nextAlert
                );

                return;
            }
        }

        if (event.defaultPrevented) {
            return;
        }

        if (event.key !== 'Backspace') {
            return;
        }

        const image =
            this.getImageImmediatelyBeforeCaret();

        if (!image) {
            return;
        }

        event.preventDefault();

        this.removeImage(
            image
        );
    }

    /**
     * Handles Backspace / Delete at the boundaries of an alert.
     *
     * @param {KeyboardEvent} event
     * @param {HTMLDivElement} alert
     *
     * @returns {boolean}
     */
    handleAlertBoundaryKey(event, alert) {
        const range =
            this.getCurrentCaretRange();

        if (!range) {
            return false;
        }

        if (
            event.key === 'Backspace'
            && this.isCaretAtStartOfNode(
                range,
                alert
            )
        ) {
            event.preventDefault();

            this.removeAlert(
                alert
            );

            return true;
        }

        if (
            event.key === 'Delete'
            && this.isCaretAtEndOfNode(
                range,
                alert
            )
        ) {
            event.preventDefault();

            this.removeAlert(
                alert
            );

            return true;
        }

        return false;
    }

    /**
     * Returns the current collapsed editor caret range.
     *
     * @returns {Range|null}
     */
    getCurrentCaretRange() {
        const selection =
            window.getSelection();

        if (
            !selection
            || selection.rangeCount === 0
            || !selection.isCollapsed
        ) {
            return null;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return null;
        }

        return range;
    }

    /**
     * Returns the alert containing the current caret.
     *
     * @returns {HTMLDivElement|null}
     */
    getContainingAlert() {
        const range =
            this.getCurrentCaretRange();

        if (!range) {
            return null;
        }

        const element =
            range.startContainer.nodeType === Node.ELEMENT_NODE
                ? range.startContainer
                : range.startContainer.parentElement;

        if (!(element instanceof Element)) {
            return null;
        }

        const alert =
            element.closest(
                `.${ALERT_EDITOR_CLASS}`
            );

        return (
            alert instanceof HTMLDivElement
                ? alert
                : null
        );
    }

    /**
     * Determines whether the caret is at the beginning of a node.
     *
     * @param {Range} range
     * @param {Node} node
     *
     * @returns {boolean}
     */
    isCaretAtStartOfNode(range, node) {
        const startRange =
            document.createRange();

        startRange.selectNodeContents(
            node
        );

        startRange.collapse(true);

        return (
            range.compareBoundaryPoints(
                Range.START_TO_START,
                startRange
            ) === 0
        );
    }

    /**
     * Determines whether the caret is at the end of a node.
     *
     * @param {Range} range
     * @param {Node} node
     *
     * @returns {boolean}
     */
    isCaretAtEndOfNode(range, node) {
        const endRange =
            document.createRange();

        endRange.selectNodeContents(
            node
        );

        endRange.collapse(false);

        return (
            range.compareBoundaryPoints(
                Range.START_TO_START,
                endRange
            ) === 0
        );
    }

    /**
     * Finds an alert immediately preceding the current caret.
     *
     * @returns {HTMLDivElement|null}
     */
    getAlertImmediatelyBeforeCaret() {
        const range =
            this.getCurrentCaretRange();

        if (!range) {
            return null;
        }

        const directChild =
            this.getDirectEditorChild(
                range.startContainer
            );

        if (!directChild) {
            return null;
        }

        if (
            !this.isCaretAtStartOfNode(
                range,
                directChild
            )
        ) {
            return null;
        }

        const previous =
            this.findPreviousMeaningfulSibling(
                directChild
            );

        return this.asAlert(previous);
    }

    /**
     * Finds an alert immediately following the current caret.
     *
     * @returns {HTMLDivElement|null}
     */
    getAlertImmediatelyAfterCaret() {
        const range =
            this.getCurrentCaretRange();

        if (!range) {
            return null;
        }

        const directChild =
            this.getDirectEditorChild(
                range.startContainer
            );

        if (!directChild) {
            return null;
        }

        if (
            !this.isCaretAtEndOfNode(
                range,
                directChild
            )
        ) {
            return null;
        }

        const next =
            this.findNextMeaningfulSibling(
                directChild
            );

        return this.asAlert(next);
    }

    /**
     * Finds the previous meaningful editor sibling.
     *
     * @param {Node} node
     *
     * @returns {Node|null}
     */
    findPreviousMeaningfulSibling(node) {
        let current =
            node.previousSibling;

        while (
            current
            && (
                current.nodeType === Node.TEXT_NODE
                    && !(current.textContent ?? '').trim()
            )
        ) {
            current =
                current.previousSibling;
        }

        while (
            current
            && current.nodeType === Node.ELEMENT_NODE
            && current.nodeName === 'BR'
        ) {
            current =
                current.previousSibling;
        }

        return current;
    }

    /**
     * Finds the next meaningful editor sibling.
     *
     * @param {Node} node
     *
     * @returns {Node|null}
     */
    findNextMeaningfulSibling(node) {
        let current =
            node.nextSibling;

        while (
            current
            && (
                current.nodeType === Node.TEXT_NODE
                    && !(current.textContent ?? '').trim()
            )
        ) {
            current =
                current.nextSibling;
        }

        while (
            current
            && current.nodeType === Node.ELEMENT_NODE
            && current.nodeName === 'BR'
        ) {
            current =
                current.nextSibling;
        }

        return current;
    }

    /**
     * Converts a node into an RTA alert reference when applicable.
     *
     * @param {Node|null} node
     *
     * @returns {HTMLDivElement|null}
     */
    asAlert(node) {
        return (
            node instanceof HTMLDivElement
            && node.classList.contains(
                ALERT_EDITOR_CLASS
            )
        )
            ? node
            : null;
    }

    /**
     * Removes an alert and places the caret into the nearest useful
     * neighboring block.
     *
     * @param {HTMLDivElement} alert
     * @param {boolean} sync
     *
     * @returns {boolean}
     */
    removeAlert(
        alert,
        sync = true
    ) {
        if (
            !(alert instanceof HTMLDivElement)
            || !this.editor.contains(alert)
        ) {
            return false;
        }

        const previous =
            alert.previousSibling;

        const next =
            alert.nextSibling;

        alert.remove();

        if (previous) {
            this.placeCaretAtEnd(
                previous
            );
        } else if (next) {
            this.placeCaretAtStart(
                next
            );
        } else {
            this.ensureEditorFallback();
        }

        if (sync) {
            this.sync();
        }

        return true;
    }

    /**
     * Removes empty alerts.
     *
     * @returns {number}
     */
    pruneEmptyAlerts() {
        let removed = 0;

        this.editor
            .querySelectorAll(
                `.${ALERT_EDITOR_CLASS}`
            )
            .forEach((alert) => {
                if (
                    alert.textContent?.trim()
                    || alert.querySelector(
                        'img, video, audio, table, iframe'
                    )
                ) {
                    return;
                }

                this.removeAlert(
                    alert,
                    false
                );

                removed++;
            });

        return removed;
    }

    /**
     * Places the caret at the start of a node.
     *
     * @param {Node} node
     *
     * @returns {void}
     */
    placeCaretAtStart(node) {
        const range =
            document.createRange();

        try {
            range.selectNodeContents(node);
            range.collapse(true);
        } catch {
            return;
        }

        const selection =
            window.getSelection();

        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(range);

        this.savedRange =
            range.cloneRange();

        this.editor.focus();
    }

    /**
     * Places the caret at the end of a node.
     *
     * @param {Node} node
     *
     * @returns {void}
     */
    placeCaretAtEnd(node) {
        const range =
            document.createRange();

        try {
            range.selectNodeContents(node);
            range.collapse(false);
        } catch {
            return;
        }

        const selection =
            window.getSelection();

        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(range);

        this.savedRange =
            range.cloneRange();

        this.editor.focus();
    }

    /**
     * Ensures the editor always contains a usable text block.
     *
     * @returns {void}
     */
    ensureEditorFallback() {
        if (
            this.editor.textContent?.trim()
            || this.editor.querySelector(
                'img, .alert, br'
            )
        ) {
            return;
        }

        const paragraph =
            document.createElement('p');

        paragraph.append(
            document.createElement('br')
        );

        this.editor.append(paragraph);

        this.placeCaretAtStart(
            paragraph
        );
    }

    /**
     * Keeps the saved editor selection up to date and synchronizes the
     * inline link popover with the current selection.
     *
     * @returns {void}
     */
    handleSelectionChange() {
        if (
            this.linkPopover
            && this.linkPopover.contains(
                document.activeElement
            )
        ) {
            return;
        }

        /*
         * An image selection intentionally owns the current browser
         * selection state. Do not let selectionchange reopen a link popover.
         */
        if (this.selectedImage) {
            this.hideLinkPopover();

            return;
        }

        const selection =
            window.getSelection();

        /*
         * When editing an existing link we intentionally clear the native
         * selection in order to avoid the blue browser highlight. The link
         * popover itself remains the visual selection, so selectionchange
         * must not immediately destroy it.
         */
        if (
            !selection
            || selection.rangeCount === 0
        ) {
            if (
                this.linkPopoverMode === 'edit'
                && this.selectedLink
            ) {
                return;
            }

            /*
             * During link creation the selected range is already preserved
             * in `linkCreationRange`.
             */
            if (
                this.linkPopoverMode === 'create'
            ) {
                return;
            }

            this.hideLinkPopover();

            return;
        }

        const range =
            selection.getRangeAt(0);

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            if (
                this.linkPopoverMode === 'edit'
                && this.selectedLink
            ) {
                return;
            }

            if (
                this.linkPopoverMode === 'create'
            ) {
                return;
            }

            this.hideLinkPopover();

            return;
        }

        this.savedRange =
            range.cloneRange();

        const link =
            this.getLinkContainingRange(
                range
            );

        if (link) {
            /*
             * Only create/reposition the editor when this isn't the link
             * already represented by the active popover.
             */
            if (
                this.linkPopoverMode !== 'edit'
                || this.selectedLink !== link
            ) {
                this.showLinkPopover(
                    link,
                    false
                );
            }

            return;
        }

        /*
         * A link-creation popover owns its saved selection and therefore
         * remains open while the user interacts with it.
         */
        if (
            this.linkPopoverMode !== 'create'
        ) {
            this.hideLinkPopover();
        }
    }

    /**
     * Prepares existing images inside the editor.
     *
     * @returns {void}
     */
    prepareExistingImages() {
        this.editor
            .querySelectorAll('img')
            .forEach((image) => {
                image.classList.add(
                    IMAGE_CLASS
                );

                image.draggable = false;
            });
    }

    /**
     * Prepares existing Bootstrap alerts inside the editor.
     *
     * @returns {void}
     */
    prepareExistingAlerts() {
        this.editor
            .querySelectorAll(
                '.alert'
            )
            .forEach((alert) => {
                alert.classList.add(
                    ALERT_EDITOR_CLASS
                );

                if (
                    !alert.dataset.rtaAlert
                ) {
                    const type =
                        Object.entries(
                            ALERT_TYPES
                        ).find(
                            ([, className]) =>
                                alert.classList.contains(
                                    className
                                )
                        )?.[0];

                    if (type) {
                        alert.dataset.rtaAlert =
                            type;
                    }
                }

                alert.setAttribute(
                    'role',
                    'alert'
                );
            });
    }

    /**
     * Selects an image using the custom RichTextarea selection UI.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    selectImage(image) {
        if (
            !(image instanceof HTMLImageElement)
            || !this.editor.contains(image)
        ) {
            return;
        }

        this.hideLinkPopover();
        clearBrowserSelection();

        if (
            this.selectedImage
            && this.selectedImage !== image
        ) {
            this.selectedImage.classList.remove(
                'rta-image-selected'
            );
        }

        this.selectedImage = image;

        this.selectedImage.classList.add(
            'rta-image-selected'
        );

        this.selectedImage.draggable = false;

        this.showSelectionFrame();
        this.positionSelectionFrame();
    }

    /**
     * Clears the current image selection.
     *
     * @returns {void}
     */
    clearImageSelection() {
        if (this.selectedImage) {
            this.selectedImage.classList.remove(
                'rta-image-selected'
            );
        }

        this.selectedImage = null;

        this.hideSelectionFrame();

        clearBrowserSelection();
    }

    /**
     * Shows the image selection frame.
     *
     * @returns {void}
     */
    showSelectionFrame() {
        if (!this.selectionFrame) {
            return;
        }

        this.selectionFrame.hidden = false;
    }

    /**
     * Hides the image selection frame.
     *
     * @returns {void}
     */
    hideSelectionFrame() {
        if (!this.selectionFrame) {
            return;
        }

        this.selectionFrame.hidden = true;
    }

    /**
     * Positions the image selection frame around the selected image.
     *
     * @returns {void}
     */
    positionSelectionFrame() {
        if (
            !this.selectionFrame
            || !this.selectedImage
            || this.selectionFrame.hidden
        ) {
            return;
        }

        if (
            !this.editor.contains(
                this.selectedImage
            )
        ) {
            this.clearImageSelection();

            return;
        }

        const imageRect =
            this.selectedImage.getBoundingClientRect();

        const rootRect =
            this.root.getBoundingClientRect();

        const borderSize = 2;

        this.selectionFrame.style.left =
            `${imageRect.left - rootRect.left - borderSize}px`;

        this.selectionFrame.style.top =
            `${imageRect.top - rootRect.top - borderSize}px`;

        this.selectionFrame.style.width =
            `${imageRect.width + borderSize * 2}px`;

        this.selectionFrame.style.height =
            `${imageRect.height + borderSize * 2}px`;
    }

    /**
     * Returns the maximum image width available inside the editor.
     *
     * @returns {number}
     */
    getEditorMaxImageWidth() {
        const computedStyle =
            window.getComputedStyle(
                this.editor
            );

        const paddingLeft =
            parseFloat(
                computedStyle.paddingLeft
            ) || 0;

        const paddingRight =
            parseFloat(
                computedStyle.paddingRight
            ) || 0;

        return Math.max(
            MIN_IMAGE_WIDTH,
            this.editor.clientWidth
                - paddingLeft
                - paddingRight
        );
    }

    /**
     * Limits an image to the editor width while preserving its aspect ratio.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    limitImageToEditorWidth(image) {
        if (
            image.naturalWidth <= 0
            || image.naturalHeight <= 0
        ) {
            return;
        }

        const maxWidth =
            this.getEditorMaxImageWidth();

        if (
            image.naturalWidth <= maxWidth
        ) {
            return;
        }

        const ratio =
            image.naturalHeight
            / image.naturalWidth;

        const width =
            Math.max(
                MIN_IMAGE_WIDTH,
                Math.round(maxWidth)
            );

        const height =
            Math.round(
                width * ratio
            );

        this.applyImageDimensions(
            image,
            width,
            height
        );
    }

    /**
     * Applies proportional dimensions to an image.
     *
     * @param {HTMLImageElement} image
     * @param {number} width
     * @param {number} height
     *
     * @returns {void}
     */
    applyImageDimensions(
        image,
        width,
        height
    ) {
        const normalizedWidth =
            Math.max(
                MIN_IMAGE_WIDTH,
                Math.round(width)
            );

        const normalizedHeight =
            Math.max(
                1,
                Math.round(height)
            );

        image.setAttribute(
            'width',
            String(normalizedWidth)
        );

        image.setAttribute(
            'height',
            String(normalizedHeight)
        );
    }

    /**
     * Handles the beginning of an image resize operation.
     *
     * @param {PointerEvent} event
     *
     * @returns {void}
     */
    handleResizePointerDown(event) {
        if (
            !this.selectedImage
            || !this.selectionFrame
        ) {
            return;
        }

        const handle =
            event.target instanceof Element
                ? event.target.closest(
                    '[data-handle]'
                )
                : null;

        if (!(handle instanceof HTMLElement)) {
            return;
        }

        const handlePosition =
            handle.dataset.handle;

        if (!handlePosition) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const rect =
            this.selectedImage.getBoundingClientRect();

        const ratio =
            rect.width > 0
                ? rect.height / rect.width
                : 1;

        this.resizeState = {
            image: this.selectedImage,
            pointerId: event.pointerId,
            handle: handlePosition,
            startX: event.clientX,
            startY: event.clientY,
            startWidth: rect.width,
            ratio,
        };

        handle.setPointerCapture?.(
            event.pointerId
        );

        window.addEventListener(
            'pointermove',
            this.boundResizePointerMove
        );

        window.addEventListener(
            'pointerup',
            this.boundResizePointerUp
        );

        window.addEventListener(
            'pointercancel',
            this.boundResizePointerUp
        );
    }

    /**
     * Handles pointer movement during image resizing.
     *
     * @param {PointerEvent} event
     *
     * @returns {void}
     */
    handleResizePointerMove(event) {
        if (!this.resizeState) {
            return;
        }

        const {
            image,
            pointerId,
            handle,
            startX,
            startY,
            startWidth,
            ratio,
        } = this.resizeState;

        if (event.pointerId !== pointerId) {
            return;
        }

        const deltaX =
            event.clientX - startX;

        const deltaY =
            event.clientY - startY;

        let directionalDelta;

        switch (handle) {
            case 'nw':
                directionalDelta =
                    (-deltaX - deltaY) / 2;
                break;

            case 'ne':
                directionalDelta =
                    (deltaX - deltaY) / 2;
                break;

            case 'sw':
                directionalDelta =
                    (-deltaX + deltaY) / 2;
                break;

            case 'se':
            default:
                directionalDelta =
                    (deltaX + deltaY) / 2;
                break;
        }

        const maxWidth =
            this.getEditorMaxImageWidth();

        const width =
            Math.min(
                Math.max(
                    MIN_IMAGE_WIDTH,
                    startWidth + directionalDelta
                ),
                maxWidth
            );

        const height =
            width * ratio;

        this.applyImageDimensions(
            image,
            width,
            height
        );

        this.positionSelectionFrame();
    }

    /**
     * Completes an image resize operation.
     *
     * @param {PointerEvent|null} event
     *
     * @returns {void}
     */
    handleResizePointerUp(event) {
        if (!this.resizeState) {
            return;
        }

        const pointerId =
            this.resizeState.pointerId;

        if (
            event
            && event.pointerId !== undefined
            && event.pointerId !== pointerId
        ) {
            return;
        }

        this.resizeState = null;

        window.removeEventListener(
            'pointermove',
            this.boundResizePointerMove
        );

        window.removeEventListener(
            'pointerup',
            this.boundResizePointerUp
        );

        window.removeEventListener(
            'pointercancel',
            this.boundResizePointerUp
        );

        this.positionSelectionFrame();

        this.sync();
    }

    /**
     * Removes an image from the editor and cleans its pending upload state.
     *
     * @param {HTMLImageElement} image
     *
     * @returns {void}
     */
    removeImage(image) {
        if (
            !(image instanceof HTMLImageElement)
        ) {
            return;
        }

        const token =
            image.dataset.rtaFileToken;

        if (token) {
            this.removePendingFile(
                token
            );
        }

        image.remove();

        if (
            this.selectedImage === image
        ) {
            this.clearImageSelection();
        }

        this.sync();
    }

    /**
     * Removes a pending local File and revokes its Object URL.
     *
     * @param {string} token
     *
     * @returns {void}
     */
    removePendingFile(token) {
        const record =
            this.pendingFiles.get(token);

        if (!record) {
            return;
        }

        this.pendingFiles.delete(token);

        if (record.objectUrl) {
            URL.revokeObjectURL(
                record.objectUrl
            );

            this.objectUrls.delete(
                record.objectUrl
            );
        }
    }

    /**
     * Removes pending Files whose tokens no longer exist in the editor.
     *
     * @returns {void}
     */
    prunePendingFiles() {
        if (
            this.pendingFiles.size === 0
        ) {
            return;
        }

        const activeTokens =
            new Set(
                Array.from(
                    this.editor.querySelectorAll(
                        'img[data-rta-file-token]'
                    )
                )
                    .map(
                        image =>
                            image.dataset.rtaFileToken
                    )
                    .filter(Boolean)
            );

        for (
            const [token] of this.pendingFiles
        ) {
            if (
                activeTokens.has(token)
            ) {
                continue;
            }

            this.removePendingFile(
                token
            );
        }
    }

    /**
     * Releases Object URLs no longer used by editor images.
     *
     * @returns {void}
     */
    pruneObjectUrls() {
        if (
            this.objectUrls.size === 0
        ) {
            return;
        }

        const activeObjectUrls =
            new Set(
                Array.from(
                    this.editor.querySelectorAll(
                        'img[src^="blob:"]'
                    )
                )
                    .map(
                        image =>
                            image.getAttribute('src')
                    )
                    .filter(Boolean)
            );

        for (
            const objectUrl of this.objectUrls
        ) {
            if (
                activeObjectUrls.has(
                    objectUrl
                )
            ) {
                continue;
            }

            URL.revokeObjectURL(
                objectUrl
            );

            this.objectUrls.delete(
                objectUrl
            );
        }
    }

    /**
     * Synchronizes the visual editor with the real textarea.
     *
     * @returns {void}
     */
    sync() {
        this.prunePendingFiles();
        this.pruneObjectUrls();

        this.textarea.value =
            this.serializeEditorHtml();

        this.textarea.dispatchEvent(
            new Event('input', {
                bubbles: true,
            })
        );

        this.positionSelectionFrame();
    }

    /**
     * Serializes editor HTML into the real form field.
     *
     * Editor-only markers are removed:
     * - image selection class;
     * - draggable attribute;
     * - RTA alert marker class;
     * - RTA alert data attribute;
     * - RTA link selection class.
     *
     * Browser-local blob URLs are removed from pending / resolved images.
     * Existing permanent image URLs are preserved.
     *
     * @returns {string}
     */
    serializeEditorHtml() {
        const container =
            document.createElement('div');

        container.innerHTML =
            this.editor.innerHTML;

        container
            .querySelectorAll('img')
            .forEach((image) => {
                image.classList.add(
                    IMAGE_CLASS
                );

                image.classList.remove(
                    'rta-image-selected'
                );

                image.removeAttribute(
                    'draggable'
                );

                if (
                    image.dataset.rtaFileToken
                    || image.dataset.uploadId
                ) {
                    image.removeAttribute(
                        'src'
                    );
                }
            });

        container
            .querySelectorAll(
                `.${ALERT_EDITOR_CLASS}`
            )
            .forEach((alert) => {
                alert.classList.remove(
                    ALERT_EDITOR_CLASS
                );

                delete alert.dataset.rtaAlert;
            });

        container
            .querySelectorAll(
                `.${LINK_SELECTED_CLASS}`
            )
            .forEach((link) => {
                link.classList.remove(
                    LINK_SELECTED_CLASS
                );
            });

        return container.innerHTML;
    }

    /**
     * Returns all pending local image Files still present in the editor.
     *
     * @returns {Array<{
     *     token: string,
     *     file: File
     * }>}
     */
    getPendingFiles() {
        this.prunePendingFiles();

        return Array.from(
            this.pendingFiles.entries()
        ).map(
            ([token, record]) => ({
                token,
                file: record.file,
            })
        );
    }

    /**
     * Resolves temporary local file tokens into server-generated upload IDs.
     *
     * @param {Object|Map<string, string>} uploads
     *
     * @returns {number}
     */
    resolveUploads(uploads) {
        const uploadMap =
            this.normalizeUploadMap(
                uploads
            );

        if (
            uploadMap.size === 0
        ) {
            return 0;
        }

        let resolvedCount = 0;

        this.editor
            .querySelectorAll(
                'img[data-rta-file-token]'
            )
            .forEach((image) => {
                const token =
                    image.dataset.rtaFileToken;

                if (
                    !token
                    || !uploadMap.has(token)
                ) {
                    return;
                }

                const uploadId =
                    uploadMap.get(token);

                if (!uploadId) {
                    return;
                }

                image.dataset.uploadId =
                    String(uploadId);

                delete image.dataset.rtaFileToken;

                this.pendingFiles.delete(
                    token
                );

                resolvedCount++;
            });

        if (
            resolvedCount > 0
        ) {
            this.sync();
        }

        return resolvedCount;
    }

    /**
     * Normalizes an upload mapping into a Map.
     *
     * @param {Object|Map<string, string>} uploads
     *
     * @returns {Map<string, string>}
     */
    normalizeUploadMap(uploads) {
        if (
            uploads instanceof Map
        ) {
            return new Map(uploads);
        }

        if (
            !uploads
            || typeof uploads !== 'object'
            || Array.isArray(uploads)
        ) {
            return new Map();
        }

        return new Map(
            Object.entries(uploads)
        );
    }

    /**
     * Returns the current serialized HTML value.
     *
     * @returns {string}
     */
    getValue() {
        return this.serializeEditorHtml();
    }

    /**
     * Returns the currently selected image.
     *
     * @returns {HTMLImageElement|null}
     */
    getSelectedImage() {
        return this.selectedImage;
    }

    /**
     * Returns the link containing a given range.
     *
     * A link is considered active when both ends of the selection belong
     * to the same anchor element.
     *
     * @param {Range|null} range
     *
     * @returns {HTMLAnchorElement|null}
     */
    getLinkContainingRange(range) {
        if (!(range instanceof Range)) {
            return null;
        }

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return null;
        }

        const startElement =
            range.startContainer.nodeType === Node.ELEMENT_NODE
                ? range.startContainer
                : range.startContainer.parentElement;

        const endElement =
            range.endContainer.nodeType === Node.ELEMENT_NODE
                ? range.endContainer
                : range.endContainer.parentElement;

        if (
            !(startElement instanceof Element)
            || !(endElement instanceof Element)
        ) {
            return null;
        }

        const startLink =
            startElement.closest('a');

        const endLink =
            endElement.closest('a');

        if (
            !(startLink instanceof HTMLAnchorElement)
            || !(endLink instanceof HTMLAnchorElement)
            || startLink !== endLink
            || !this.editor.contains(startLink)
        ) {
            return null;
        }

        return startLink;
    }

    /**
     * Returns the anchor element under the given event target.
     *
     * @param {EventTarget|null} target
     *
     * @returns {HTMLAnchorElement|null}
     */
    getLinkFromTarget(target) {
        if (!(target instanceof Element)) {
            return null;
        }

        const link =
            target.closest('a');

        return (
            link instanceof HTMLAnchorElement
            && this.editor.contains(link)
        )
            ? link
            : null;
    }

    /**
     * Selects a link and opens its inline editor.
     *
     * @param {HTMLAnchorElement} link
     * @param {boolean} clearSelection
     *
     * @returns {void}
     */
    selectLink(
        link,
        clearSelection = false
    ) {
        if (
            !(link instanceof HTMLAnchorElement)
            || !this.editor.contains(link)
        ) {
            return;
        }

        this.clearImageSelection();

        if (
            this.selectedLink
            && this.selectedLink !== link
        ) {
            this.selectedLink.classList.remove(
                LINK_SELECTED_CLASS
            );
        }

        this.selectedLink = link;

        this.selectedLink.classList.add(
            LINK_SELECTED_CLASS
        );

        /*
         * The saved Range remains untouched here. That is important:
         * the visual selection can be cleared while the logical editor
         * selection still survives for later toolbar operations.
         */
        this.showLinkPopover(
            link,
            clearSelection
        );
    }

    /**
     * Shows the inline editor for an existing link.
     *
     * @param {HTMLAnchorElement} link
     * @param {boolean} clearSelection
     *
     * @returns {void}
     */
    showLinkPopover(
        link,
        clearSelection = false
    ) {
        if (
            !(link instanceof HTMLAnchorElement)
            || !this.editor.contains(link)
        ) {
            return;
        }

        /*
         * Do not destroy a currently active editor for the same link.
         * This also prevents selectionchange loops from rebuilding it.
         */
        if (
            this.linkPopoverMode === 'edit'
            && this.selectedLink === link
            && this.linkPopover
        ) {
            if (clearSelection) {
                clearBrowserSelection();
            }

            this.repositionLinkPopover();

            return;
        }

        this.hideLinkPopover();

        this.selectedLink = link;

        this.selectedLink.classList.add(
            LINK_SELECTED_CLASS
        );

        this.linkPopoverMode = 'edit';
        this.linkCreationRange = null;

        if (clearSelection) {
            clearBrowserSelection();
        }

        this.createLinkPopover(
            link.getBoundingClientRect(),
            link.getAttribute('href') ?? '',
            'edit'
        );
    }

    /**
     * Shows the inline editor for creating a new link around the given text
     * selection.
     *
     * @param {Range} range
     *
     * @returns {void}
     */
    showCreateLinkPopover(range) {
        if (!(range instanceof Range)) {
            return;
        }

        if (
            range.collapsed
            || !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return;
        }

        this.hideLinkPopover();

        this.selectedLink = null;
        this.linkPopoverMode = 'create';

        this.linkCreationRange =
            range.cloneRange();

        /*
         * Keep the same range as the editor's canonical saved selection.
         * The browser may lose it as soon as the URL input receives focus.
         */
        this.savedRange =
            range.cloneRange();

        this.createLinkPopover(
            range.getBoundingClientRect(),
            '',
            'create'
        );
    }

    /**
     * Creates the DOM structure of the link popover.
     *
     * @param {DOMRect} anchorRect
     * @param {string} value
     * @param {'edit'|'create'} mode
     *
     * @returns {void}
     */
    createLinkPopover(
        anchorRect,
        value,
        mode
    ) {
        const popover =
            document.createElement('div');

        popover.className =
            'rta-link-popover';

        popover.setAttribute(
            'role',
            'dialog'
        );

        const label =
            document.createElement('label');

        label.className =
            'form-label small mb-1';

        label.textContent =
            'URL';

        const input =
            document.createElement('input');

        input.type = 'url';

        input.className =
            'form-control form-control-sm rta-link-popover__input';

        input.value = value;

        input.autocomplete = 'off';
        input.spellcheck = false;

        const actions =
            document.createElement('div');

        actions.className =
            'd-flex gap-2 mt-2';

        const submit =
            document.createElement('button');

        submit.type = 'button';

        submit.className =
            'btn btn-primary btn-sm flex-fill';

        submit.dataset.rtaLinkSave =
            '';

        submit.textContent =
            mode === 'edit'
                ? 'Изменить'
                : 'Создать';

        actions.append(
            submit
        );

        if (mode === 'edit') {
            const remove =
                document.createElement('button');

            remove.type = 'button';

            remove.className =
                'btn btn-outline-danger btn-sm';

            remove.dataset.rtaLinkRemove =
                '';

            remove.textContent =
                'Удалить';

            actions.append(
                remove
            );
        } else {
            const cancel =
                document.createElement('button');

            cancel.type = 'button';

            cancel.className =
                'btn btn-outline-secondary btn-sm';

            cancel.dataset.rtaLinkCancel =
                '';

            cancel.textContent =
                'Отмена';

            actions.append(
                cancel
            );
        }

        popover.append(
            label,
            input,
            actions
        );

        popover.addEventListener(
            'click',
            this.boundLinkPopoverClick
        );

        popover.addEventListener(
            'keydown',
            this.boundLinkPopoverKeyDown
        );

        this.root.append(
            popover
        );

        this.linkPopover =
            popover;

        this.positionLinkPopover(
            anchorRect
        );

        requestAnimationFrame(() => {
            if (
                this.linkPopover !== popover
            ) {
                return;
            }

            input.focus();

            if (mode === 'edit') {
                input.select();
            }
        });
    }

    /**
     * Positions the link popover relative to its anchor rectangle.
     *
     * @param {DOMRect} anchorRect
     *
     * @returns {void}
     */
    positionLinkPopover(anchorRect) {
        if (!(this.linkPopover instanceof HTMLElement)) {
            return;
        }

        const rootRect =
            this.root.getBoundingClientRect();

        const popoverRect =
            this.linkPopover.getBoundingClientRect();

        const horizontalPadding = 8;
        const verticalGap = 8;

        let left =
            anchorRect.left
            - rootRect.left
            + (
                anchorRect.width
                - popoverRect.width
            ) / 2;

        left = Math.max(
            horizontalPadding,
            Math.min(
                left,
                this.root.clientWidth
                    - popoverRect.width
                    - horizontalPadding
            )
        );

        let top =
            anchorRect.bottom
            - rootRect.top
            + verticalGap;

        const upperSpace =
            anchorRect.top
            - rootRect.top;

        if (
            top + popoverRect.height
                > this.root.clientHeight
            && upperSpace
                > popoverRect.height
                    + verticalGap
        ) {
            top =
                anchorRect.top
                - rootRect.top
                - popoverRect.height
                - verticalGap;
        }

        this.linkPopover.style.left =
            `${Math.round(left)}px`;

        this.linkPopover.style.top =
            `${Math.round(top)}px`;
    }

    /**
     * Handles buttons inside the link popover.
     *
     * @param {MouseEvent} event
     *
     * @returns {void}
     */
    handleLinkPopoverClick(event) {
        const target =
            event.target;

        if (!(target instanceof Element)) {
            return;
        }

        if (
            target.closest(
                '[data-rta-link-save]'
            )
        ) {
            event.preventDefault();

            this.saveLinkFromPopover();

            return;
        }

        if (
            target.closest(
                '[data-rta-link-remove]'
            )
        ) {
            event.preventDefault();

            this.removeSelectedLink();

            return;
        }

        if (
            target.closest(
                '[data-rta-link-cancel]'
            )
        ) {
            event.preventDefault();

            this.hideLinkPopover();
        }
    }

    /**
     * Handles keyboard interaction inside the link popover.
     *
     * Enter saves.
     * Escape closes without applying changes.
     *
     * @param {KeyboardEvent} event
     *
     * @returns {void}
     */
    handleLinkPopoverKeyDown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            this.saveLinkFromPopover();

            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();

            this.hideLinkPopover();
        }
    }

    /**
     * Saves an existing link or creates a new one from the popover.
     *
     * @returns {void}
     */
    saveLinkFromPopover() {
        if (!(this.linkPopover instanceof HTMLElement)) {
            return;
        }

        const input =
            this.linkPopover.querySelector(
                '.rta-link-popover__input'
            );

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const url =
            input.value.trim();

        if (!url) {
            input.focus();

            return;
        }

        if (
            this.linkPopoverMode === 'edit'
            && this.selectedLink
        ) {
            const link =
                this.selectedLink;

            link.setAttribute(
                'href',
                url
            );

            this.hideLinkPopover();

            this.sync();

            /*
             * Re-open the editor on the same link so the user can continue
             * editing without having to find the link again.
             */
            this.selectLink(
                link,
                true
            );

            return;
        }

        if (
            this.linkPopoverMode !== 'create'
            || !(this.linkCreationRange instanceof Range)
        ) {
            return;
        }

        /*
         * Restore the exact range saved before the URL input stole focus.
         */
        this.savedRange =
            this.linkCreationRange.cloneRange();

        this.restoreSelection();

        const rangeBeforeCommand =
            this.savedRange.cloneRange();

        document.execCommand(
            'createLink',
            false,
            url
        );

        const selection =
            window.getSelection();

        const range =
            selection
            && selection.rangeCount > 0
                ? selection.getRangeAt(0)
                : rangeBeforeCommand;

        const link =
            this.getLinkContainingRange(
                range
            );

        /*
         * As a fallback, locate an anchor intersecting the original
         * creation range when browser selection changed after execCommand.
         */
        const fallbackLink =
            link
            ?? this.findLinkIntersectingRange(
                rangeBeforeCommand
            );

        this.hideLinkPopover();

        this.sync();

        if (fallbackLink) {
            this.selectLink(
                fallbackLink,
                true
            );
        }
    }

    /**
     * Finds an anchor intersecting the supplied range.
     *
     * @param {Range} range
     *
     * @returns {HTMLAnchorElement|null}
     */
    findLinkIntersectingRange(range) {
        if (!(range instanceof Range)) {
            return null;
        }

        if (
            !this.editor.contains(
                range.commonAncestorContainer
            )
        ) {
            return null;
        }

        const links =
            this.editor.querySelectorAll('a');

        for (const link of links) {
            try {
                if (
                    range.intersectsNode(link)
                ) {
                    return link;
                }
            } catch {
                continue;
            }
        }

        return null;
    }

    /**
     * Removes a link while preserving its textual/content nodes.
     *
     * @param {HTMLAnchorElement|null} link
     *
     * @returns {void}
     */
    removeSelectedLink(
        link = this.selectedLink
    ) {
        if (
            !(link instanceof HTMLAnchorElement)
            || !this.editor.contains(link)
        ) {
            return;
        }

        const parent =
            link.parentNode;

        if (!parent) {
            return;
        }

        const children = [];

        while (link.firstChild) {
            const child =
                link.firstChild;

            children.push(
                child
            );

            parent.insertBefore(
                child,
                link
            );
        }

        link.remove();

        this.hideLinkPopover();

        const lastNode =
            children.at(-1);

        if (lastNode) {
            this.placeCaretAtEnd(
                lastNode
            );
        }

        this.sync();
    }

    /**
     * Hides and destroys the active link popover.
     *
     * @returns {void}
     */
    hideLinkPopover() {
        if (this.linkPopover) {
            this.linkPopover.removeEventListener(
                'click',
                this.boundLinkPopoverClick
            );

            this.linkPopover.removeEventListener(
                'keydown',
                this.boundLinkPopoverKeyDown
            );

            this.linkPopover.remove();
        }

        if (this.selectedLink) {
            this.selectedLink.classList.remove(
                LINK_SELECTED_CLASS
            );
        }

        this.selectedLink = null;
        this.linkPopover = null;
        this.linkPopoverMode = null;
        this.linkCreationRange = null;
    }

    /**
     * Closes the link editor when the user clicks outside the RTA.
     *
     * @param {PointerEvent} event
     *
     * @returns {void}
     */
    handleDocumentPointerDown(event) {
        if (!this.linkPopover) {
            return;
        }

        const target =
            event.target;

        if (
            target instanceof Node
            && (
                this.root.contains(target)
                || this.linkPopover.contains(target)
            )
        ) {
            return;
        }

        this.hideLinkPopover();
    }

    /**
     * Repositions an active link popover after viewport changes.
     *
     * @returns {void}
     */
    handleWindowResize() {
        this.repositionLinkPopover();
        this.positionSelectionFrame();
    }

    /**
     * Repositions an active link popover while scrolling.
     *
     * @returns {void}
     */
    handleWindowScroll() {
        this.repositionLinkPopover();
        this.positionSelectionFrame();
    }

    /**
     * Repositions the currently active link popover using its current anchor.
     *
     * @returns {void}
     */
    repositionLinkPopover() {
        if (
            !(this.linkPopover instanceof HTMLElement)
        ) {
            return;
        }

        if (
            this.linkPopoverMode === 'edit'
            && this.selectedLink
        ) {
            this.positionLinkPopover(
                this.selectedLink.getBoundingClientRect()
            );

            return;
        }

        if (
            this.linkPopoverMode === 'create'
            && this.linkCreationRange instanceof Range
        ) {
            this.positionLinkPopover(
                this.linkCreationRange.getBoundingClientRect()
            );
        }
    }

    /**
     * Destroys the RichTextarea instance and releases all resources.
     *
     * @returns {void}
     */
    destroy() {
        this.editor.removeEventListener(
            'input',
            this.boundEditorInput
        );

        this.editor.removeEventListener(
            'blur',
            this.boundSync
        );

        this.editor.removeEventListener(
            'pointerdown',
            this.boundEditorPointerDown
        );

        this.editor.removeEventListener(
            'click',
            this.boundEditorClick
        );

        this.editor.removeEventListener(
            'dragstart',
            this.boundEditorDragStart
        );

        this.editor.removeEventListener(
            'keydown',
            this.boundEditorKeyDown
        );

        this.editor.removeEventListener(
            'keyup',
            this.boundEditorKeyUp
        );

        this.editor.removeEventListener(
            'mouseup',
            this.boundEditorMouseUp
        );

        document.removeEventListener(
            'selectionchange',
            this.boundSelectionChange
        );

        document.removeEventListener(
            'pointerdown',
            this.boundDocumentPointerDown
        );

        window.removeEventListener(
            'resize',
            this.boundWindowResize
        );

        window.removeEventListener(
            'scroll',
            this.boundWindowScroll,
            true
        );

        window.removeEventListener(
            'pointermove',
            this.boundResizePointerMove
        );

        window.removeEventListener(
            'pointerup',
            this.boundResizePointerUp
        );

        window.removeEventListener(
            'pointercancel',
            this.boundResizePointerUp
        );

        if (
            this.toolbar instanceof HTMLElement
        ) {
            this.toolbar.removeEventListener(
                'mousedown',
                this.boundToolbarMouseDown
            );

            this.toolbar.removeEventListener(
                'click',
                this.boundToolbarClick
            );
        }

        if (
            this.imageInput instanceof HTMLInputElement
        ) {
            this.imageInput.removeEventListener(
                'change',
                this.boundImageInputChange
            );

            this.imageInput.remove();
        }

        if (this.selectionFrame) {
            this.selectionFrame
                .querySelectorAll(
                    '[data-handle]'
                )
                .forEach((handle) => {
                    handle.removeEventListener(
                        'pointerdown',
                        this.boundResizePointerDown
                    );
                });

            this.selectionFrame.remove();
        }

        this.hideLinkPopover();

        for (
            const objectUrl of this.objectUrls
        ) {
            URL.revokeObjectURL(
                objectUrl
            );
        }

        this.objectUrls.clear();
        this.pendingFiles.clear();

        this.clearImageSelection();

        this.savedRange = null;

        initializedRichTextareas.delete(
            this.textarea
        );

        richTextareaInstances.delete(
            this.textarea
        );

        delete this.textarea.__richTextarea;
    }
}

/**
 * Initializes one RichTextarea control.
 *
 * @param {HTMLTextAreaElement} textarea
 *
 * @returns {RichTextarea|null}
 */
function initRichTextarea(textarea) {
    if (
        !(textarea instanceof HTMLTextAreaElement)
        || initializedRichTextareas.has(textarea)
    ) {
        return null;
    }

    try {
        const instance =
            new RichTextarea(textarea);

        initializedRichTextareas.add(
            textarea
        );

        richTextareaInstances.set(
            textarea,
            instance
        );

        textarea.__richTextarea =
            instance;

        return instance;
    } catch (error) {
        console.error(
            '[RichTextarea] Failed to initialize:',
            error
        );

        return null;
    }
}

/**
 * Initializes all RichTextarea controls inside a scope.
 *
 * @param {ParentNode} scope
 *
 * @returns {RichTextarea[]}
 */
export function initRichTextareas(
    scope = document
) {
    const instances = [];

    scope
        .querySelectorAll(
            'textarea[data-rich-textarea]'
        )
        .forEach((textarea) => {
            const instance =
                initRichTextarea(
                    textarea
                );

            if (instance) {
                instances.push(instance);
            }
        });

    return instances;
}

/**
 * Returns the RichTextarea instance associated with a real textarea.
 *
 * @param {HTMLTextAreaElement} textarea
 *
 * @returns {RichTextarea|null}
 */
export function getRichTextarea(
    textarea
) {
    if (
        !(textarea instanceof HTMLTextAreaElement)
    ) {
        return null;
    }

    return (
        richTextareaInstances.get(
            textarea
        ) ?? null
    );
}

/**
 * Returns all initialized RichTextarea instances inside a scope.
 *
 * @param {ParentNode} scope
 *
 * @returns {RichTextarea[]}
 */
export function getRichTextareas(
    scope = document
) {
    const instances = [];

    scope
        .querySelectorAll(
            'textarea[data-rich-textarea]'
        )
        .forEach((textarea) => {
            const instance =
                getRichTextarea(
                    textarea
                );

            if (instance) {
                instances.push(instance);
            }
        });

    return instances;
}

document.addEventListener(
    'DOMContentLoaded',
    () => {
        initRichTextareas();
    }
);
