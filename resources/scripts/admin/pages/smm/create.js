/**
 * @file resources/scripts/admin/pages/smm/create.js
 * @description Admin page module for smm/create; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/smm/create.scss';
export function init() {
    const select = document.getElementById('promotable_type');
    const hidden = document.getElementById('promotable_id');
    select?.addEventListener('change', () => { hidden.value = select.selectedOptions[0]?.dataset.id || ''; });
}
