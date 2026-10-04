<div class="admin-aside-stack">
    <section class="admin-surface"><div class="admin-surface__body"><x-ui.form.media-dropzone name="cover" ratio="16x9" :existing-url="$course->cover_url"/><x-ui.form.input name="publish_date" label="Дата публикации" type="datetime-local" :value="$course->publish_date?->format('Y-m-d\TH:i')" hint="Для scheduled дата обязательна."/></div></section>
    <section class="admin-surface" data-course-teachers>
        <div class="admin-surface__head"><div><h3 class="h6 mb-1">Преподаватели</h3><div class="admin-section-note">Typeahead добавляет преподавателя, Sortable задаёт порядок. Сохранение — отдельный AJAX-action.</div></div></div>
        <div class="admin-surface__body">
            <form action="{{ route('admin.courses.teachers.update',$course) }}" method="POST" data-ajax-form="true" data-teachers-form>
                @csrf
                <x-ui.form.typeahead name="teacher_picker" id="course-teacher-picker" label="Добавить преподавателя" endpoint="{{ route('admin.teachers.options') }}" data-course-teacher-picker/>
                <x-ui.form.sortable name="teacher_structure" :value="$course->teachers->map(fn($t)=>['value'=>(string)$t->id,'label'=>$t->title])->all()" :button="false"/>
                <div data-teacher-hidden></div>
                <x-ui.form.change-tracker/>
            </form>
        </div>
    </section>
</div>
