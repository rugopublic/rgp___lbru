/**
 * @file resources/scripts/admin/pages/recipes/categories/index.js
 * @description Admin page module for recipes/categories/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/recipes/categories/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
