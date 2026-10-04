/**
 * UI Textarea
 *
 * Features:
 * - automatic vertical resize
 * - min/max rows
 * - character meter
 * - regexp validation
 * - ripple reaction
 * - paste reaction
 * - validation error hiding on change
 */

const initializedTextareas = new WeakSet();

function initTextarea(root) {
    if (!root || initializedTextareas.has(root)) {
        return;
    }

    const textarea = root.querySelector('[data-textarea-input]');

    if (!textarea) {
        return;
    }

    initializedTextareas.add(root);

    const minRows = parseInt(root.dataset.minRows || '1', 10);
    const maxRows = parseInt(root.dataset.maxRows || '8', 10);
    const autosize = root.dataset.autosize !== 'false';
    const rippleEnabled = root.dataset.ripple !== 'false';

    const meter = root.querySelector('[data-textarea-meter]');
    const meterBar = root.querySelector('[data-textarea-meter-bar]');
    const meterLabel = root.querySelector('[data-textarea-meter-label]');

    const label = root.querySelector('[data-textarea-label]');
    const errorTrigger = root.querySelector('[data-textarea-error-trigger]');
    const errorPopover = root.querySelector('[data-textarea-error]');

    const rippleLayer = root.querySelector('[data-textarea-ripple-layer]');

    const getLineHeight = () => {
        const styles = window.getComputedStyle(textarea);
        const lineHeight = parseFloat(styles.lineHeight);

        if (!Number.isNaN(lineHeight)) {
            return lineHeight;
        }

        const fontSize = parseFloat(styles.fontSize);

        return fontSize * 1.5;
    };

    const getVerticalPadding = () => {
        const styles = window.getComputedStyle(textarea);

        return (
            parseFloat(styles.paddingTop || 0) +
            parseFloat(styles.paddingBottom || 0) +
            parseFloat(styles.borderTopWidth || 0) +
            parseFloat(styles.borderBottomWidth || 0)
        );
    };

    const calculateHeightForRows = (rows) => {
        return (getLineHeight() * rows) + getVerticalPadding();
    };

    const resize = () => {
        if (!autosize) {
            return;
        }

        textarea.style.height = 'auto';

        const styles = window.getComputedStyle(textarea);

        const minHeight = calculateHeightForRows(minRows);
        const maxHeight = calculateHeightForRows(maxRows);

        const contentHeight = textarea.scrollHeight;

        let nextHeight = Math.max(
            contentHeight,
            minHeight
        );

        if (nextHeight > maxHeight) {
            nextHeight = maxHeight;
            textarea.style.overflowY = 'auto';
        } else {
            textarea.style.overflowY = 'hidden';
        }

        textarea.style.height = `${nextHeight}px`;

        /*
         * Keep box sizing correct even if a global stylesheet
         * changes it.
         */
        if (styles.boxSizing === 'content-box') {
            textarea.style.height =
                `${nextHeight - getVerticalPadding()}px`;
        }
    };

    const updateMeter = () => {
        if (!meter || !meterBar || !meterLabel) {
            return;
        }

        const maxLength = textarea.maxLength;

        if (!maxLength || maxLength < 0) {
            meter.hidden = true;
            return;
        }

        meter.hidden = false;

        const length = textarea.value.length;
        const percentage = Math.min(
            100,
            (length / maxLength) * 100
        );

        meterBar.style.width = `${percentage}%`;
        meterLabel.textContent = `${length} / ${maxLength}`;

        meter.classList.remove(
            'is-warning',
            'is-danger',
            'is-complete'
        );

        if (percentage >= 100) {
            meter.classList.add('is-complete');
        } else if (percentage >= 90) {
            meter.classList.add('is-danger');
        } else if (percentage >= 75) {
            meter.classList.add('is-warning');
        }
    };

    const hideError = () => {
        root.classList.remove('has-error');

        if (label) {
            label.classList.remove('is-error');
        }

        textarea.classList.remove('is-invalid');
        textarea.setAttribute('aria-invalid', 'false');

        if (errorPopover) {
            errorPopover.hidden = true;
        }

        if (errorTrigger) {
            errorTrigger.setAttribute(
                'aria-expanded',
                'false'
            );
        }
    };

    const showError = () => {
        root.classList.add('has-error');

        if (label) {
            label.classList.add('is-error');
        }

        textarea.classList.add('is-invalid');
        textarea.setAttribute('aria-invalid', 'true');
    };

    const validatePattern = (value) => {
        const pattern = textarea.dataset.pattern;

        if (!pattern) {
            return true;
        }

        try {
            return new RegExp(pattern).test(value);
        } catch (error) {
            console.warn(
                '[UI Textarea] Invalid regexp pattern:',
                pattern,
                error
            );

            return true;
        }
    };

    const emitRipple = (type = 'accepted') => {
        if (!rippleEnabled || !rippleLayer) {
            return;
        }

        /*
         * Remove old ripple nodes.
         */
        rippleLayer
            .querySelectorAll('.ui-form-textarea__ripple')
            .forEach((ripple) => ripple.remove());

        const ripple = document.createElement('span');

        ripple.className =
            `ui-form-textarea__ripple is-${type}`;

        rippleLayer.appendChild(ripple);

        requestAnimationFrame(() => {
            ripple.classList.add('is-active');
        });

        ripple.addEventListener(
            'animationend',
            () => ripple.remove(),
            { once: true }
        );
    };

    const markAccepted = () => {
        root.classList.remove('is-rejected');
        root.classList.add('is-accepted');

        window.setTimeout(() => {
            root.classList.remove('is-accepted');
        }, 240);
    };

    const markRejected = () => {
        root.classList.remove('is-accepted');
        root.classList.add('is-rejected');

        window.setTimeout(() => {
            root.classList.remove('is-rejected');
        }, 260);
    };

    const handleInput = () => {
        /*
         * input fires for:
         * - normal typing
         * - Backspace
         * - Delete
         * - Enter
         * - paste
         * - drag/drop
         * - IME changes
         */
        hideError();

        updateMeter();
        resize();

        /*
         * Pattern is validation, not destructive filtering.
         * The server remains the final authority.
         */
        const valid = validatePattern(textarea.value);

        if (valid) {
            markAccepted();
            emitRipple('accepted');
        } else {
            markRejected();
            emitRipple('rejected');
        }
    };

    const handleKeydown = (event) => {
        /*
         * Give Delete / Backspace a direct visual reaction.
         * The actual value update is still handled by input.
         */
        if (
            event.key === 'Backspace' ||
            event.key === 'Delete'
        ) {
            hideError();

            window.requestAnimationFrame(() => {
                resize();
                updateMeter();

                emitRipple('erase');
                markAccepted();
            });
        }
    };

    const handlePaste = () => {
        hideError();

        window.requestAnimationFrame(() => {
            resize();
            updateMeter();

            emitRipple('paste');
            markAccepted();
        });
    };

    const handleErrorToggle = (event) => {
        event.preventDefault();

        if (!errorPopover) {
            return;
        }

        const willOpen = errorPopover.hidden;

        errorPopover.hidden = !willOpen;

        errorTrigger?.setAttribute(
            'aria-expanded',
            String(willOpen)
        );
    };

    textarea.addEventListener('input', handleInput);
    textarea.addEventListener('keydown', handleKeydown);
    textarea.addEventListener('paste', handlePaste);

    errorTrigger?.addEventListener(
        'click',
        handleErrorToggle
    );

    /*
     * Initial state.
     */
    updateMeter();

    /*
     * Resize after fonts/layout are available.
     */
    requestAnimationFrame(resize);

    /*
     * If browser restores textarea value from BFCache/autofill.
     */
    window.addEventListener('pageshow', () => {
        updateMeter();
        resize();
    });

    /*
     * Public-ish API for other admin scripts.
     */
    root.uiTextarea = {
        resize,
        updateMeter,
        hideError,
        showError,
        emitRipple,
    };
}

export function initTextareas(scope = document) {
    scope
        .querySelectorAll('[data-ui-textarea]')
        .forEach(initTextarea);
}

document.addEventListener('DOMContentLoaded', () => {
    initTextareas();
});
