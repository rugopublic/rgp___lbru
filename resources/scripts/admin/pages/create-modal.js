/**
 * @file resources/scripts/admin/pages/create-modal.js
 * @description Admin page module for create-modal; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports initCreateModals
 */

export function initCreateModals() {
    const openId = document.body.dataset.adminOpenModal;
        document.querySelectorAll('[data-admin-create-form]').forEach(form => {
        if (form.dataset.bound === '1') return;
        form.dataset.bound = '1';
        const modal = form.closest('[data-admin-create-modal]');
        const errors = form.querySelector('[data-admin-create-errors]');
        const submit = form.querySelector('[data-admin-create-submit]');
        const spinner = form.querySelector('[data-spinner]');

        const showErrors = payload => {
            if (!errors) return;
            const messages = [];
            if (payload?.errors) Object.values(payload.errors).flat().forEach(v => messages.push(v));
            if (payload?.message && !messages.length) messages.push(payload.message);
            errors.innerHTML = messages.length ? `<ul>${messages.map(m => `<li>${escapeHtml(m)}</li>`).join('')}</ul>` : '';
            errors.hidden = !messages.length;
        };
        const escapeHtml = value => String(value).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));

        form.addEventListener('submit', async event => {
            event.preventDefault();
            showErrors({});
            submit?.setAttribute('disabled', 'disabled');
            spinner?.classList.remove('d-none');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    showErrors(payload);
                    return;
                }
                if (payload.redirect) window.location.assign(payload.redirect);
                else window.location.reload();
            } catch (error) {
                showErrors({ message: 'Не удалось связаться с сервером. Данные не потеряны — попробуйте ещё раз.' });
            } finally {
                submit?.removeAttribute('disabled');
                spinner?.classList.add('d-none');
            }
        });
    });
    if (openId) {
        const element = document.getElementById(openId);
        if (element && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}
