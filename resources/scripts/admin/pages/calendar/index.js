/**
 * @file resources/scripts/admin/pages/calendar/index.js
 * @description Admin page module for calendar/index; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/calendar/index.scss';
export function init() {
 const modalEl=document.getElementById('dayModal'); if(!modalEl) return;
 document.querySelectorAll('[data-calendar-date]').forEach(day=>day.addEventListener('click',async()=>{
  const date=day.dataset.calendarDate, body=document.getElementById('modalBody'), title=document.getElementById('modalDate'); title.textContent=date; body.innerHTML='<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>'; bootstrap.Modal.getOrCreateInstance(modalEl).show();
  try{const r=await fetch(`${window.adminCalendarDataUrl}?date=${encodeURIComponent(date)}`,{headers:{Accept:'application/json'}}); const d=await r.json(); if(!d.publications?.length){body.innerHTML='<div class="admin-empty py-4">На этот день ничего не запланировано.</div>';return;} body.innerHTML=`<div class="list-group list-group-flush">${d.publications.map(p=>`<a class="list-group-item list-group-item-action py-3" href="${p.url}"><strong>${esc(p.title)}</strong><div class="small text-muted mt-1">${esc(p.category)} · ${esc(p.status_label||p.status)}</div></a>`).join('')}</div>`;}catch(e){body.innerHTML='<div class="alert alert-danger">Не удалось загрузить публикации. Обновите страницу и повторите.</div>';}}));
 function esc(v){return String(v).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));}
}
