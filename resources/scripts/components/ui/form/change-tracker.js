/**
 * @file resources/scripts/components/ui/form/change-tracker.js
 * @description Reusable browser component module for ui/form/change-tracker.js; owns the client-side behavior of this UI primitive.
 */

class ChangeTracker {
    constructor(trackedForm) {
        if (!(trackedForm instanceof HTMLFormElement)) {
            throw new TypeError("The 'trackedForm' must be a valid HTML form node.");
        }

        this.form = trackedForm;
        this.tracker = this.form.querySelector(".change-tracker");
        if (!this.tracker) throw new TypeError("The provided form doesn't have a change tracker.");

        this.indicator = this.tracker.querySelector(".indicator");
        if (!this.indicator) throw new TypeError("Tracker missing '.indicator' node.");

        this.counter = this.tracker.querySelector(".count");
        if (!this.counter) throw new TypeError("Tracker missing '.count' node.");

        this.changelogContainer = document.querySelector('#formChangeLogModal [data-change-list]');
        if (!this.changelogContainer) throw new TypeError("Page missing '#formChangeLogModal [data-change-list]'.");

        this.defaults = {};
        this.changes = {};
        this.labels = {};
        this.types = {};
    }

    get count() {
        return Object.keys(this.changes).length;
    }

    get payload() {
        const payloadData = {};

        Object.entries(this.changes).forEach(([name, value]) => {
            const cleanKey = name.endsWith('[]')
                ? name.slice(0, -2)
                : name;

            if (cleanKey.endsWith('_remove')) {
                const targetKey = cleanKey.slice(0, -'_remove'.length);

                if (String(value) === '1') {
                    payloadData[targetKey] = null;
                }

                return;
            }

            if (Array.isArray(value)) {
                payloadData[cleanKey] = value.map(
                    item => this._unwrapFieldValue(item)
                );

                return;
            }

            payloadData[cleanKey] = this._unwrapFieldValue(value);
        });

        return payloadData;
    }

    _unwrapFieldValue(val) {
        if (val === null || val === undefined) return '';
        if (typeof val === 'object' && 'checked' in val) {
            return val.checked ? val.value : '';
        }
        if (typeof val === 'object' && 'isFileDescriptor' in val) {
            return val.rawFiles; // Returns real File array or empty array
        }
        return val;
    }

    getLabel(field) {
        const fallbackTitle = 'Untitled';
        if (!(field instanceof HTMLElement)) return fallbackTitle;

        let labelText = '';

        if ('labels' in field && field.labels && field.labels.length > 0) {
            labelText = field.labels[0].textContent || '';
        } else if (field.id) {
            const explicitLabel = this.form.querySelector(`label[for="${CSS.escape(field.id)}"]`);
            if (explicitLabel) {
                labelText = explicitLabel.textContent || '';
            }
        }

        if (!labelText.trim() && field.hasAttribute('aria-labelledby')) {
            const labelId = field.getAttribute('aria-labelledby');

            const ariaLabelledByNode = document.getElementById(labelId);
            if (ariaLabelledByNode) {
                labelText = ariaLabelledByNode.textContent || '';
            }
        }

        if (!labelText.trim() && field.hasAttribute('aria-label')) {
            labelText = field.getAttribute('aria-label') || '';
        }

        if (!labelText || !labelText.trim()) {
            return fallbackTitle;
        }

        let title = labelText.trim();

        title = title.replace(/\*/g, ' ')
                     .replace(/\s+/g, ' ')
                     .trim();

        return title || fallbackTitle;
    }

    formatRichTextChange(val) {
        if (!val || typeof val !== 'string') return '';
        const doc = new DOMParser().parseFromString(val, 'text/html');
        const hasContent = doc.body.textContent.trim().length > 0 || doc.body.querySelector('img, video, audio, table');
        return hasContent ? 'Содержимое изменено.' : '';
    }

    formatValue(val) {
        if (val === null || val === undefined) return '';
        if (Array.isArray(val)) {
            return val.map(item => this.formatValue(item)).filter(str => str.trim() !== '').join(', ');
        }
        if (typeof val === 'object') {
            if ('checked' in val) {
                return val.checked ? `Checked (${val.value})` : `Unchecked (${val.value})`;
            }
            if ('isFileDescriptor' in val) {
                return val.filesMetadata.map(f => f.name).join(', ') || 'Файл удален';
            }
            return JSON.stringify(val);
        }
        if (typeof val === 'string' && val.includes('<') && val.includes('>')) {
            return this.formatRichTextChange(val);
        }
        return String(val);
    }

    updateChangelog() {
        this.changelogContainer.textContent = '';
        Object.entries(this.changes).forEach(([name, value]) => {
            const displayName = this.labels[name] || (name.endsWith('[]') ? name.slice(0, -2) : name);
            const changeDiv = document.createElement('div');
            changeDiv.classList.add('change');

            const nameDiv = document.createElement('div');
            nameDiv.classList.add('name');
            nameDiv.textContent = displayName;

            const formatter = this.types[name] === 'rich-text' ? this.formatRichTextChange : this.formatValue;
            const oldText = formatter.call(this, this.defaults[name]);
            const newText = formatter.call(this, value);

            const oldDiv = document.createElement('div');
            oldDiv.classList.add('old');
            oldDiv.textContent = oldText;

            const newDiv = document.createElement('div');
            newDiv.classList.add('new');
            newDiv.textContent = newText;

            changeDiv.append(nameDiv);
            if (oldText.trim() !== '') changeDiv.append(oldDiv);
            if (newText.trim() !== '') changeDiv.append(newDiv);
            this.changelogContainer.append(changeDiv);
        });
    }

    updateIndicator() {
        if (this.count > 0) this.indicator.classList.add('active');
        else this.indicator.classList.remove('active');
    }

    updateCounter() {
        this.counter.innerHTML = this.count;
        this.updateIndicator();
        this.updateChangelog();
    }

    extractFieldValue(field) {
        if (!field) return '';

        if (field.nodeName === "INPUT" && field.type === "file") {
            const rawFiles = Array.from(field.files || []);
            return {
                isFileDescriptor: true,
                rawFiles: rawFiles,
                filesMetadata: rawFiles.map(file => ({ name: file.name, size: file.size }))
            };
        }

        if (field.nodeName === "INPUT" && field.type === "checkbox") {
            return { checked: field.checked, value: field.value };
        }

        if (field.nodeName === "INPUT" && field.type === "radio") {
            return field.checked ? field.value : null;
        }

        return field.value;
    }

    getLiveFormValue(name) {
        const isArray = name.endsWith("[]");
        const matchingFields = this.form.querySelectorAll(`[name="${CSS.escape(name)}"]`);

        if (!isArray) {
            if (matchingFields.length > 0 && matchingFields[0].type === 'radio') {
                const checkedRadio = Array.from(matchingFields).find(r => r.checked);
                return checkedRadio ? checkedRadio.value : '';
            }
            return matchingFields.length > 0 ? this.extractFieldValue(matchingFields[0]) : '';
        }

        const values = [];
        matchingFields.forEach(el => {
            if (el.type === 'checkbox') {
                if (el.checked) values.push({ checked: true, value: el.value });
            } else if (el.type === 'radio') {
                if (el.checked) values.push(el.value);
            } else {
                values.push(el.value);
            }
        });
        return values;
    }

    handleEvent(event) {
        const field = event.target;
        if (!field || !field.name) return;

        if (!(field.name in this.labels)) {
            this.labels[field.name] = this.getLabel(field);
        }

        const currentLiveValue = this.getLiveFormValue(field.name);
        this.register(field.name, currentLiveValue);
    }

    isEqual(a, b) {
        if (a === b) return true;
        if (a === null || b === null || typeof a !== typeof b) return false;

        if (Array.isArray(a)) {
            if (!Array.isArray(b) || a.length !== b.length) return false;
            return a.every((item, index) => this.isEqual(item, b[index]));
        }

        if (typeof a === 'object') {
            const keysA = Object.keys(a);
            const keysB = Object.keys(b);
            if (keysA.length !== keysB.length) return false;
            return keysA.every(key => key in b && this.isEqual(a[key], b[key]));
        }

        return false;
    }

    register(name, newValue) {
        if (!(name in this.defaults)) {
            this.defaults[name] = Array.isArray(newValue) ? [] : '';
        }

        this.changes[name] = newValue;

        if (this.isEqual(newValue, this.defaults[name])) {
            delete this.changes[name];
        }

        this.updateCounter();
    }

    initialize() {
        const processedNames = new Set();

        for (const field of this.form.elements) {
            if (!field.name || processedNames.has(field.name)) continue;
            processedNames.add(field.name);

            this.types[field.name] = field.hasAttribute('data-rich-textarea') ? 'rich-text' : 'default';
            this.labels[field.name] = this.getLabel(field);

            this.defaults[field.name] = this.getLiveFormValue(field.name);
        }

        this.updateCounter();

        this.form.addEventListener('input', (e) => this.handleEvent(e));
        this.form.addEventListener('change', (e) => this.handleEvent(e));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const trackedForms = document.querySelectorAll('form:has(.change-tracker)');

    for (const form of trackedForms) {
        try {
            const tracker = new ChangeTracker(form);
            tracker.initialize();

            form.__changeTracker = tracker;

            console.debug('[ChangeTracker] Cleanly initialized for form node:', form);
        } catch (initError) {
            console.error('[ChangeTracker] Failed to initialize tracker on target form:', initError);
        }
    }
});
