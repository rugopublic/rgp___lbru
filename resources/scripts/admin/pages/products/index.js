/**
 * @file resources/scripts/admin/pages/products/index.js
 * @description Admin page module for products/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/products/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
