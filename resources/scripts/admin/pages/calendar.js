/**
 * @file resources/scripts/admin/pages/calendar.js
 * @description Admin page module for calendar; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports initCalendar
 */

export function initCalendar() {
    const modalEl = document.getElementById('dayModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    document.querySelectorAll('[data-calendar-date]').forEach(day => {
        day.addEventListener('click', async () => {
            const date = day.dataset.calendarDate;
            const body = document.getElementById('modalBody');
            const title = document.getElementById('modalDate');
            title.textContent = date;
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            try {
                const response = await fetch(`${window.adminCalendarDataUrl}?date=${encodeURIComponent(date)}`, { headers: { Accept:'application/json' } });
                const data = await response.json();
                if (!data.publications?.length) {
                    body.innerHTML = '<div class="admin-empty py-4">На этот день ничего не запланировано.</div>';
                    return;
                }
                body.innerHTML = `<div class="list-group list-group-flush">${data.publications.map(pub => `<a class="list-group-item list-group-item-action py-3" href="${pub.url}"><strong>${escapeHtml(pub.title)}</strong><div class="small text-muted mt-1">${escapeHtml(pub.category)} · ${escapeHtml(pub.status_label || pub.status)}</div></a>`).join('')}</div>`;
            } catch {
                body.innerHTML = '<div class="alert alert-danger">Не удалось загрузить публикации. Обновите страницу и повторите.</div>';
            }
        });
    });
    function escapeHtml(value){return String(value).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));}
}
