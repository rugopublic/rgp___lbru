/**
 * @file resources/scripts/components/ui/form/input.js
 * @description Reusable browser component module for ui/form/input.js; owns the client-side behavior of this UI primitive.
 */

/*
|--------------------------------------------------------------------------
| UI INPUT
|--------------------------------------------------------------------------
*/

class UiInput {
    constructor(root) {
        this.root = root;

        this.input = root.querySelector('[data-ui-input-control]');
        this.wrap = root.querySelector('[data-ui-input-wrap]');

        this.meter = root.querySelector('[data-ui-input-meter]');
        this.meterBar = root.querySelector('[data-ui-input-meter-bar]');
        this.meterLabel = root.querySelector('[data-ui-input-meter-label]');

        this.label = root.querySelector('[data-ui-input-label]');

        this.error = root.querySelector('[data-ui-input-error]');
        this.errorToggle = root.querySelector('[data-ui-input-error-toggle]');

        this.maxlength = Number(
            root.dataset.uiInputMaxlength || this.input?.maxLength || 0
        );

        this.patternSource = root.dataset.uiInputPattern || '';

        this.pattern = null;

        if (this.patternSource) {
            try {
                this.pattern = new RegExp(this.patternSource);
            } catch (error) {
                console.warn(
                    '[UiInput] Invalid regexp:',
                    this.patternSource,
                    error
                );
            }
        }

        this.init();
    }

    init() {
        if (!this.input) {
            return;
        }

        this.updateMeter();

        /*
         * keydown:
         *
         * Здесь ловим именно действие пользователя,
         * а не изменение значения.
         *
         * Поэтому Backspace / Delete больше не теряются.
         */
        this.input.addEventListener('keydown', (event) => {
            this.handleKeydown(event);
        });

        /*
         * input:
         *
         * Срабатывает для:
         * - вставки мышкой;
         * - Ctrl+V;
         * - автозаполнения;
         * - drag & drop;
         * - программного изменения через пользовательский ввод.
         */
        this.input.addEventListener('input', (event) => {
            this.handleInput(event);
        });

        /*
         * Любое изменение снимает Laravel validation error.
         */
        this.input.addEventListener('input', () => {
            this.clearServerError();
        });

        if (this.errorToggle) {
            this.errorToggle.addEventListener('click', () => {
                this.toggleError();
            });
        }
    }

    handleKeydown(event) {
        /*
         * Системные комбинации не считаем отдельным символом.
         */
        if (
            event.ctrlKey ||
            event.metaKey ||
            event.altKey
        ) {
            return;
        }

        /*
         * Backspace.
         */
        if (event.key === 'Backspace') {
            if (this.input.selectionStart !== this.input.selectionEnd) {
                this.createRipple('delete');
            } else if (this.input.selectionStart > 0) {
                this.createRipple('delete');
            }

            return;
        }

        /*
         * Delete.
         */
        if (event.key === 'Delete') {
            if (
                this.input.selectionStart !== this.input.selectionEnd ||
                this.input.selectionStart < this.input.value.length
            ) {
                this.createRipple('delete');
            }

            return;
        }

        /*
         * Enter для однострочного input здесь не имеет смысла.
         */
        if (event.key === 'Enter') {
            return;
        }

        /*
         * Tab / Escape / стрелки / Home / End и т.п.
         */
        if (event.key.length !== 1) {
            return;
        }

        /*
         * Если regexp задан — проверяем символ ещё ДО input.
         */
        if (this.pattern) {
            const accepted = this.isCharacterAllowed(event.key);

            this.createRipple(
                accepted ? 'success' : 'danger'
            );

            if (!accepted) {
                event.preventDefault();
            }

            return;
        }

        /*
         * Без regexp обычный символ считается принятым.
         */
        this.createRipple('success');
    }

    handleInput(event) {
        this.updateMeter();

        /*
         * Вставка текста.
         *
         * keydown здесь не происходит для многих способов paste,
         * поэтому input — правильное место для дополнительной реакции.
         */
        if (
            event.inputType === 'insertFromPaste' ||
            event.inputType === 'insertText' ||
            event.inputType === 'insertFromDrop'
        ) {
            /*
             * Для paste не делаем огромную волну:
             * всего одна мягкая реакция.
             */
            this.createRipple('success');
        }

        /*
         * Если значение стало валидным —
         * можно вернуть обычный цвет meter.
         */
        this.updateValidationVisualState();
    }

    isCharacterAllowed(character) {
        if (!this.pattern) {
            return true;
        }

        /*
         * Проверяем будущую строку, а не только символ.
         *
         * Это позволяет regexp ограничивать:
         * - допустимые символы;
         * - структуру строки;
         * - длину/формат.
         */
        const start = this.input.selectionStart ?? this.input.value.length;
        const end = this.input.selectionEnd ?? start;

        const nextValue =
            this.input.value.slice(0, start) +
            character +
            this.input.value.slice(end);

        return this.pattern.test(nextValue);
    }

    updateMeter() {
        if (!this.meter || !this.meterBar || !this.meterLabel) {
            return;
        }

        const valueLength = Array.from(this.input.value).length;

        /*
         * maxlength может отсутствовать.
         */
        if (!this.maxlength) {
            this.meterBar.style.width = '0%';
            this.meterLabel.textContent = `${valueLength}`;
            return;
        }

        const percentage = Math.min(
            100,
            (valueLength / this.maxlength) * 100
        );

        this.meterBar.style.width = `${percentage}%`;

        this.meterLabel.textContent =
            `${valueLength} / ${this.maxlength}`;

        this.meter.classList.remove(
            'is-warning',
            'is-good',
            'is-full'
        );

        if (percentage >= 100) {
            this.meter.classList.add('is-full');
        } else if (percentage >= 80) {
            this.meter.classList.add('is-good');
        } else if (percentage >= 60) {
            this.meter.classList.add('is-warning');
        }
    }

    createRipple(type = 'success') {
        if (!this.wrap) {
            return;
        }

        /*
         * Если пользователь печатает быстро,
         * старые волны не мешают новым.
         */
        const ripple = document.createElement('span');

        ripple.className = 'ui-input__ripple';

        if (type === 'danger') {
            ripple.classList.add('is-danger');
        } else if (type === 'delete') {
            ripple.classList.add('is-delete');
        } else {
            ripple.classList.add('is-success');
        }

        this.wrap.appendChild(ripple);

        /*
         * После animation удаляем DOM-элемент.
         */
        ripple.addEventListener(
            'animationend',
            () => ripple.remove(),
            { once: true }
        );
    }

    clearServerError() {
        if (!this.root.classList.contains('has-error')) {
            return;
        }

        this.root.classList.remove('has-error');
        this.root.classList.add('is-corrected');

        this.input.classList.remove('is-invalid');

        if (this.label) {
            this.label.classList.remove('is-error');
        }

        if (this.error) {
            this.error.classList.remove('is-visible');
            this.error.setAttribute('aria-hidden', 'true');
        }

        if (this.errorToggle) {
            this.errorToggle.setAttribute('aria-expanded', 'false');
        }
    }

    toggleError() {
        if (!this.error) {
            return;
        }

        const visible = this.error.classList.toggle('is-visible');

        this.error.setAttribute(
            'aria-hidden',
            visible ? 'false' : 'true'
        );

        if (this.errorToggle) {
            this.errorToggle.setAttribute(
                'aria-expanded',
                visible ? 'true' : 'false'
            );
        }
    }

    updateValidationVisualState() {
        /*
         * Сейчас meter отвечает только за длину.
         *
         * Дополнительную validation-логику сюда можно будет
         * подключить позже, не ломая компонент.
         */
    }
}


/*
|--------------------------------------------------------------------------
| Initialization
|--------------------------------------------------------------------------
*/

function initUiInputs(scope = document) {
    scope
        .querySelectorAll('[data-ui-input]')
        .forEach((element) => {
            if (element.__uiInput) {
                return;
            }

            element.__uiInput = new UiInput(element);
        });
}


/*
|--------------------------------------------------------------------------
| Initial load
|--------------------------------------------------------------------------
*/

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        () => initUiInputs()
    );
} else {
    initUiInputs();
}
