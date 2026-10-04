/**
 * @file resources/scripts/admin/pages/reference/ingredients/index.js
 * @description Admin page module for reference/ingredients/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/reference/ingredients/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
