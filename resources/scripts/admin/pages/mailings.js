/**
 * @file resources/scripts/admin/pages/mailings.js
 * @description Admin page module for mailings; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports initMailings
 */

export function initMailings() {
    const subject = document.getElementById('mailing_subject');
    const body = document.getElementById('mailing_body');
    const previewSubject = document.getElementById('mailingPreviewSubject');
    const previewBody = document.getElementById('mailingPreviewBody');
    const update = () => {
        if (previewSubject) previewSubject.textContent = subject?.value || 'Тема письма';
        if (previewBody) previewBody.textContent = body?.value || 'Текст письма появится здесь.';
    };
    subject?.addEventListener('input', update); body?.addEventListener('input', update); update();
}
