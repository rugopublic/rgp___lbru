@php($modules=$course->modules->map(fn($m)=>['id'=>$m->id,'title'=>$m->title])->all())
<div class="modal fade" id="courseLessonCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.courses.modules.store', $course) }}" data-course-lesson-create data-admin-create-form>
                @csrf
                <div class="modal-header"><h2 class="modal-title fs-5">Новый урок</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label" for="course-lesson-title">Название</label><input id="course-lesson-title" class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label" for="course-lesson-module">Модуль</label><select id="course-lesson-module" class="form-select" name="module_id" required>@foreach($modules as $module)<option value="{{ $module['id'] }}">{{ $module['title'] }}</option>@endforeach</select></div>
                    <div class="alert alert-danger" hidden data-admin-create-errors></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Отмена</button><button type="submit" class="btn btn-primary" data-admin-create-submit>Создать</button></div>
            </form>
        </div>
    </div>
</div>
