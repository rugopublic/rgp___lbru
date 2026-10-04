/**
 * @file resources/scripts/admin/shared/sortable-picker.js
 * @description Admin shared module for resources/scripts/admin/shared/sortable-picker.js; provides reusable or page-level administration behavior.
 * @exports bindTypeaheadToSortable
 */

import { getSortable, initSortable } from '@/scripts/components/ui/form/sortable';

/**
 * Add selected Typeahead values to a custom Sortable root list.
 * The Sortable component remains domain-agnostic; this adapter owns the
 * domain relation policy outside the component itself.
 */
export async function bindTypeaheadToSortable({ sortableRoot, typeaheadRoot, duplicateMessage = 'Элемент уже добавлен.' }) {
    const sortable = await initSortable(sortableRoot);
    const valueInput = typeaheadRoot?.querySelector('[data-typeahead-value]');
    const clearButton = typeaheadRoot?.querySelector('[data-typeahead-clear]');
    if (!valueInput) return sortable;

    const selectedLabel = typeaheadRoot.querySelector('[data-typeahead-selected-label]');

    const resetPicker = () => clearButton?.click();

    valueInput.addEventListener('change', () => {
        const value = String(valueInput.value || '');
        if (!value || !sortable.rootItemsRoot) return;

        const duplicate = [...sortable.rootItemsRoot.querySelectorAll(':scope > [data-sortable-item]')]
            .some(item => String(item.dataset.sortableItemValue || '') === value);

        if (duplicate) {
            resetPicker();
            return;
        }

        const label = selectedLabel?.textContent?.trim() || value;
        const item = sortable.createItemElement({ value, label });
        sortable.rootItemsRoot.append(item);
        sortable.sync();
        resetPicker();
    });

    return sortable;
}
