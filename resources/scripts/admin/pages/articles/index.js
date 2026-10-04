/**
 * @file resources/scripts/admin/pages/articles/index.js
 * @description Admin page module for articles/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/articles/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
