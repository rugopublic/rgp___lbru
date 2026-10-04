/**
 * @file resources/scripts/admin/pages/quizzes/index.js
 * @description Admin page module for quizzes/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/quizzes/index.scss';
import { initCreateModals } from '@/scripts/admin/pages/create-modal';

export function init() {
    initCreateModals();
}
