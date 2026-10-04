/**
 * @file resources/scripts/admin/delete-flow.js
 * @description Admin shared module for resources/scripts/admin/delete-flow.js; provides reusable or page-level administration behavior.
 * @exports initDeleteFlow
 */

import * as bootstrap from 'bootstrap';

export function initDeleteFlow() {
    const modalElement = document.getElementById('deletionConsentModal');
    if (!modalElement) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const targetForm = modalElement.querySelector('form:last-of-type');
    const checkbox = modalElement.querySelector('#confirmDeleteCourse');
    const submit = targetForm?.querySelector('[type="submit"]');

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.editor-delete-btn');
        if (!button) return;
        const form = button.closest('form');
        if (!form) return;
        modalElement.__deleteSource = form;
        modalElement.querySelectorAll('.modal-material-title').forEach(e => e.textContent = form.dataset.deleteTitle || 'запись');
        modalElement.querySelectorAll('.modal-material-type-label').forEach(e => e.textContent = form.dataset.deleteLabel || 'запись');
        const consequences = form.querySelector('[data-deletion-consequences]');
        const target = modalElement.querySelector('.modal-deletion-consequences');
        target.innerHTML = consequences?.innerHTML || '<li><i class="bi bi-dot"></i>Запись будет скрыта из рабочего списка.</li>';
        if (checkbox) checkbox.checked = false;
        if (submit) submit.disabled = true;
        modal.show();
    });

    checkbox?.addEventListener('change', () => { submit.disabled = !checkbox.checked; });

    targetForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = modalElement.__deleteSource;
        const endpoint = form?.dataset.deleteEndpoint;
        if (!form || !endpoint) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        submit.disabled = true;
        fetch(endpoint, { method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept':'application/json','Content-Type':'application/json'}, body: JSON.stringify({_method:'DELETE'}) })
            .then(r => r.json())
            .then(data => { if (data.redirect) window.location.assign(data.redirect); else modal.hide(); })
            .catch(() => { submit.disabled = false; });
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDeleteFlow, {once:true});
else initDeleteFlow();
