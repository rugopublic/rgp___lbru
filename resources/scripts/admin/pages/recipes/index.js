/**
 * @file resources/scripts/admin/pages/recipes/index.js
 * @description Admin page module for recipes/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/recipes/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
