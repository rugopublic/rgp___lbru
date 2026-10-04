/**
 * @file resources/scripts/admin/pages/courses/edit.js
 * @description Admin page module for courses/edit; binds page-specific UI behavior and delegates shared form workflows to reusable components.
 * @exports init
 */

import '@/styles/admin/pages/courses/edit.scss';
import { initSortable } from '@/scripts/components/ui/form/sortable';
import { bindTypeaheadToSortable } from '@/scripts/admin/shared/sortable-picker';

export async function init() {
    const structureRoot = document.querySelector('[data-course-structure] [data-sortable]');
    if (structureRoot) {
        const sortable = await initSortable(structureRoot);
        structureRoot.querySelectorAll('[data-sortable-group-add], [data-sortable-group-add-item]').forEach(button => button.hidden = true);
        structureRoot.addEventListener('sortable:group-add', () => structureRoot.querySelectorAll('[data-sortable-group-add], [data-sortable-group-add-item]').forEach(button => button.hidden = true));
        sortable.groupsRoot?.addEventListener('click', event => {
            if (event.target.closest('[data-sortable-group-add-item]')) event.stopImmediatePropagation();
        }, true);
    }

    const lessonModalForm = document.querySelector('[data-course-lesson-create]');
    const lessonModuleSelect = document.querySelector('#course-lesson-module');
    if (lessonModalForm && lessonModuleSelect) {
        const base = lessonModalForm.action.replace(/\/modules$/, '');
        const syncLessonAction = () => { lessonModalForm.action = `${base}/modules/${encodeURIComponent(lessonModuleSelect.value)}/lessons`; };
        lessonModuleSelect.addEventListener('change', syncLessonAction);
        syncLessonAction();
    }

    const teacherSortableRoot = document.querySelector('[data-course-teachers] [data-sortable]');
    const teacherPicker = document.querySelector('[data-course-teacher-picker]');
    if (teacherSortableRoot && teacherPicker) {
        const sortable = await bindTypeaheadToSortable({ sortableRoot: teacherSortableRoot, typeaheadRoot: teacherPicker });
        const form = document.querySelector('[data-teachers-form]');
        const syncHidden = () => {
            const box = form?.querySelector('[data-teacher-hidden]');
            if (!box) return;
            box.replaceChildren();
            const ids = sortable.serialize().items.map(item => item.value).filter(Boolean);
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'teacher_ids[]'; input.value = id; box.append(input);
            });
            box.dataset.dirty = '1';
            box.querySelector('input')?.dispatchEvent(new Event('change', { bubbles: true }));
        };
        teacherSortableRoot.addEventListener('sortable:item-remove', syncHidden);
        teacherSortableRoot.addEventListener('sortable:reorder', syncHidden);
        teacherSortableRoot.addEventListener('sortable:item-add', syncHidden);
        syncHidden();
    }
}
