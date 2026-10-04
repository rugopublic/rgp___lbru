<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__body">
            <div class="row g-3">
                <div class="col-lg-8"><x-ui.form.input name="title" label="Название" :value="$course->title" required/></div>
                <div class="col-lg-4"><x-ui.form.typeahead name="category_id" id="course-category" label="Категория" :value="$course->category_id" :options="$categories" endpoint="{{ route('admin.courses.categories.options') }}"/></div>
                <div class="col-md-4"><x-ui.form.dropdown name="difficulty" label="Сложность" :value="$course->difficulty?->value" :options="['beginner'=>'Начинающий','intermediate'=>'Средний','advanced'=>'Продвинутый']" fallback="Выберите уровень"/></div>
                <div class="col-md-4"><x-ui.form.input name="price" label="Цена, ₽" type="number" step="0.01" min="0" :value="$course->price"/></div>
                <div class="col-md-4"><x-ui.form.dropdown name="publish_status" label="Статус публикации" :value="$course->publish_status?->value" :options="['draft'=>'Черновик','scheduled'=>'Запланирован','published'=>'Опубликован']" fallback="Выберите статус"/></div>
            </div>
            <x-ui.form.input name="summary" label="Краткое обещание" :value="$course->summary" maxlength="512"/>
            <x-ui.form.rich-textarea name="pitch" id="course-pitch" input-id="course-pitch-input" labelledby="course-pitch-label" :value="$course->pitch"/>
            <div class="row g-3">
                <div class="col-md-6"><x-ui.form.textarea name="objective" id="course-objective" label="Зачем курс" :value="$course->objective" maxlength="1000"/></div>
                <div class="col-md-6"><x-ui.form.textarea name="skills" id="course-skills" label="Что умеет ученик" :value="$course->skills" maxlength="1000"/></div>
                <div class="col-md-6"><x-ui.form.textarea name="audience" id="course-audience" label="Кому подходит" :value="$course->audience" maxlength="1000"/></div>
                <div class="col-md-6"><x-ui.form.textarea name="features" id="course-features" label="Особенности" :value="$course->features" maxlength="1000"/></div>
                <div class="col-md-6"><x-ui.form.textarea name="prerequisites" id="course-prerequisites" label="Предварительные требования" :value="$course->prerequisites" maxlength="1000"/></div>
                <div class="col-md-6"><x-ui.form.textarea name="preferables" id="course-preferables" label="Желательные навыки" :value="$course->preferables" maxlength="1000"/></div>
            </div>
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__head">
            <div><h3 class="h6 mb-1">Структура курса</h3><div class="admin-section-note">Модули сверху; уроки можно переносить между модулями курса. Порядок — только через hidden/input state.</div></div>
            <div class="d-flex gap-2"><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#courseModuleCreateModal">Добавить модуль</button><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#courseLessonCreateModal">Добавить урок</button></div>
        </div>
        <div class="admin-surface__body">
            <div class="admin-structure" data-course-structure>
                <x-ui.form.sortable name="structure" :value="['groups'=>[] ,'items'=>[]]" :button="false" :grouped="true">
                    @foreach($course->modules as $module)
                        <x-ui.form.sortable.group :group-id="$module->id" :label="$module->title">
                            @foreach($module->lessons as $lesson)
                                <x-ui.form.sortable.item :value="$lesson->id" :data-sortable-item-label-value="$lesson->title"><a class="text-decoration-none" href="{{ route('admin.courses.lessons.edit',[$course,$lesson]) }}">{{ $lesson->title }}</a></x-ui.form.sortable.item>
                            @endforeach
                        </x-ui.form.sortable.group>
                    @endforeach
                </x-ui.form.sortable>
            </div>
            <p class="admin-section-note mt-2 mb-0">После reorder форма отправит структуру как обычное поле <code>structure</code>; сервер проверит принадлежность каждого урока именно этому курсу.</p>
        </div>
    </section>
</div>
