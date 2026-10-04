/**
 * @file resources/scripts/admin/pages/reference/edit.js
 * @description Admin page module for reference/edit; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/reference/ingredients/edit.scss';
import { bindTypeaheadToSortable } from '@/scripts/admin/shared/sortable-picker';

export async function init() {
    for (const picker of document.querySelectorAll('[data-relation-picker]')) {
        const name = picker.dataset.relationPicker;
        const sortableRoot = document.querySelector(`[data-relation-sortable="${CSS.escape(name)}"] [data-sortable]`);
        if (!sortableRoot) continue;
        await bindTypeaheadToSortable({ sortableRoot, typeaheadRoot: picker });
    }
}
