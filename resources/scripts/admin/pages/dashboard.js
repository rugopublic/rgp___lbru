/**
 * @file resources/scripts/admin/pages/dashboard.js
 * @description Admin page module for dashboard; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/dashboard.scss';
export function init() { document.querySelectorAll('[data-dashboard-refresh]').forEach(button => button.addEventListener('click', () => window.location.reload())); }
