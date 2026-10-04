/**
 * @file resources/scripts/admin/pages/mailings/create.js
 * @description Admin page module for mailings/create; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/mailings/create.scss';
export function init(){const s=document.getElementById('mailing_subject'),b=document.getElementById('mailing_body'),ps=document.getElementById('mailingPreviewSubject'),pb=document.getElementById('mailingPreviewBody'); const u=()=>{if(ps)ps.textContent=s?.value||'Тема письма';if(pb)pb.textContent=b?.value||'Текст письма появится здесь.'};s?.addEventListener('input',u);b?.addEventListener('input',u);u();}
