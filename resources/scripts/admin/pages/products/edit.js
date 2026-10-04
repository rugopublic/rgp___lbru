/**
 * @file resources/scripts/admin/pages/products/edit.js
 * @description Admin page module for products/edit; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/products/edit.scss';
import { bindTypeaheadToSortable } from '@/scripts/admin/shared/sortable-picker';

export async function init() {
    for (const picker of document.querySelectorAll('[data-relation-picker]')) {
        const name = picker.dataset.relationPicker;
        const sortableRoot = document.querySelector(`[data-relation-sortable="${CSS.escape(name)}"] [data-sortable]`);
        if (!sortableRoot) continue;
        await bindTypeaheadToSortable({ sortableRoot, typeaheadRoot: picker });
    }
}
