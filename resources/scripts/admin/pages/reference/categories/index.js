/**
 * @file resources/scripts/admin/pages/reference/categories/index.js
 * @description Admin page module for reference/categories/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/reference/categories/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
