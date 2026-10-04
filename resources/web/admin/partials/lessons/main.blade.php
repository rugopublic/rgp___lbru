<div class="admin-editor-stack">
    <section class="admin-surface">
        <div class="admin-surface__head"><div><h3 class="h6 mb-1">Основные данные</h3><div class="admin-section-note">Урок может быть сохранён независимо от готовности публикационных данных курса.</div></div></div>
        <div class="admin-surface__body">
            <x-ui.form.input name="title" label="Название" :value="$lessonModel->title" required/>
            <x-ui.form.textarea name="description" id="lesson-description" label="Описание" :value="$lessonModel->description"/>
            <div class="row g-3"><div class="col-md-6"><x-ui.form.input name="duration_minutes" label="Длительность, мин" type="number" min="0" :value="$lessonModel->duration_minutes"/></div><div class="col-md-6 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_preview" value="1" id="lesson-preview" @checked($lessonModel->is_preview)><label class="form-check-label" for="lesson-preview">Доступен как preview</label></div></div></div>
            <x-ui.form.media-dropzone name="video" ratio="16x9" :existing-url="$lessonModel->video_file_url"/>
        </div>
    </section>
    <section class="admin-surface">
        <div class="admin-surface__head"><div><h3 class="h6 mb-1">Шаги урока</h3><div class="admin-section-note">Порядок определяется payload формы; удалённые шаги исчезают транзакционно.</div></div><button type="button" class="btn btn-outline-secondary btn-sm" data-lesson-step-add><i class="bi bi-plus-lg me-1"></i>Добавить шаг</button></div>
        <div class="admin-surface__body">
            <div id="lesson-steps" class="admin-list" data-sortable data-sortable-axis="y" data-sortable-disabled="false">
                @foreach($lessonModel->steps as $step)
                    <div class="admin-list-item" data-sortable-item data-lesson-step>
                        <input type="hidden" name="steps[{{ $loop->index }}][id]" value="{{ $step->id }}"><input type="hidden" name="steps[{{ $loop->index }}][sort_order]" value="{{ $loop->index }}" data-step-order>
                        <button type="button" class="btn btn-link text-muted p-0 me-2" data-drag-handle title="Переместить шаг" aria-label="Переместить шаг"><i class="bi bi-grip-vertical fs-5"></i></button>
                        <div class="flex-grow-1">
                            <div class="row g-3"><div class="col-md-8"><x-ui.form.input name="steps[{{ $loop->index }}][title]" label="Заголовок" :value="$step->title"/></div><div class="col-md-4"><x-ui.form.dropdown name="steps[{{ $loop->index }}][type]" label="Тип" :value="$step->type" :options="['text'=>'Текст','video'=>'Видео','quiz'=>'Quiz']" fallback="Тип шага"/></div></div>
                            <x-ui.form.textarea name="steps[{{ $loop->index }}][content]" id="step-content-{{ $step->id }}" label="Содержание" :value="$step->content"/>
                            <div class="row g-3"><div class="col-md-8"><x-ui.form.input name="steps[{{ $loop->index }}][video_url]" label="Видео URL" type="url" :value="$step->video_url"/></div><div class="col-md-4"><x-ui.form.input name="steps[{{ $loop->index }}][duration_seconds]" label="Длительность, сек" type="number" min="0" :value="$step->duration_seconds"/></div></div>
                        </div>
                        <button type="button" class="btn btn-link text-danger p-0 ms-2" data-lesson-step-remove aria-label="Удалить шаг"><i class="bi bi-trash3"></i></button>
                    </div>
                @endforeach
            </div>
            <template id="lesson-step-template"><div class="admin-list-item" data-sortable-item data-lesson-step><input type="hidden" data-field="id"><input type="hidden" data-field="sort_order" data-step-order><button type="button" class="btn btn-link text-muted p-0 me-2" data-drag-handle title="Переместить шаг" aria-label="Переместить шаг"><i class="bi bi-grip-vertical fs-5"></i></button><div class="flex-grow-1"><div class="row g-3"><div class="col-md-8"><label class="form-label">Заголовок</label><input class="form-control" data-field="title"></div><div class="col-md-4"><label class="form-label">Тип</label><select class="form-select" data-field="type"><option value="text">Текст</option><option value="video">Видео</option><option value="quiz">Quiz</option></select></div></div><div class="mt-3"><label class="form-label">Содержание</label><textarea class="form-control" rows="5" data-field="content"></textarea></div><div class="row g-3 mt-1"><div class="col-md-8"><label class="form-label">Видео URL</label><input class="form-control" type="url" data-field="video_url"></div><div class="col-md-4"><label class="form-label">Длительность, сек</label><input class="form-control" type="number" min="0" data-field="duration_seconds"></div></div></div><button type="button" class="btn btn-link text-danger p-0 ms-2" data-lesson-step-remove aria-label="Удалить шаг"><i class="bi bi-trash3"></i></button></div></template>
        </div>
    </section>

    <section class="admin-surface" data-lesson-quiz>
        <div class="admin-surface__head">
            <div>
                <h3 class="h6 mb-1">Проверка знаний</h3>
                <div class="admin-section-note">Редактор использует существующий quiz-домен: вопросы и варианты сохраняются внутри текущего quiz, без отдельной LMS-подсистемы.</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="quiz[enabled]" value="1" id="lesson-quiz-enabled" @checked($quiz)>
                <label class="form-check-label" for="lesson-quiz-enabled">Использовать quiz</label>
            </div>
        </div>
        <div class="admin-surface__body">
            <div data-quiz-editor @class(['d-none' => !$quiz])>
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <x-ui.form.input name="quiz[title]" label="Название quiz" :value="$quiz?->title ?? ($lessonModel->title.' — проверка')" :required="(bool) $quiz" />
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="quiz[is_published]" value="1" id="quiz-published" @checked($quiz?->is_published)>
                            <label class="form-check-label" for="quiz-published">Доступен ученикам</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <x-ui.form.textarea name="quiz[description]" id="quiz-description" label="Описание" :value="$quiz?->description" />
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-semibold">Вопросы</div>
                        <div class="admin-section-note">У каждого вопроса должно быть минимум два варианта и ровно один правильный.</div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-quiz-question-add>
                        <i class="bi bi-plus-lg me-1"></i>Добавить вопрос
                    </button>
                </div>

                <div id="lesson-quiz-questions" class="admin-editor-stack" data-quiz-questions data-sortable data-sortable-axis="y">
                    @foreach(($quiz?->questions ?? collect()) as $question)
                        <article class="admin-surface" data-sortable-item data-quiz-question>
                            <div class="admin-surface__head">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-link text-muted p-0" data-drag-handle aria-label="Переместить вопрос"><i class="bi bi-grip-vertical fs-5"></i></button>
                                    <span class="small text-muted">Вопрос {{ $loop->iteration }}</span>
                                </div>
                                <button type="button" class="btn btn-link text-danger p-0" data-quiz-question-remove aria-label="Удалить вопрос"><i class="bi bi-trash3"></i></button>
                            </div>
                            <div class="admin-surface__body">
                                <input type="hidden" data-quiz-field="id" value="{{ $question->id }}">
                                <div class="ui-form-field">
                                    <label class="form-label" for="quiz-question-{{ $question->id }}">Вопрос <span class="required-mark">*</span></label>
                                    <textarea class="form-control" id="quiz-question-{{ $question->id }}" rows="3" data-quiz-field="question_text" name="quiz[questions][{{ $loop->index }}][question_text]" required>{{ $question->question_text }}</textarea>
                                </div>
                                <div class="mt-3">
                                    <div class="small fw-semibold mb-2">Варианты ответа</div>
                                    <div class="admin-editor-stack" data-quiz-options>
                                        @foreach($question->options as $option)
                                            <div class="d-flex align-items-center gap-2" data-quiz-option>
                                                <input type="hidden" data-quiz-field="id" value="{{ $option->id }}">
                                                <input type="hidden" data-quiz-correct-value data-quiz-field="is_correct" value="{{ (int) $option->is_correct }}">
                                                <input class="form-check-input mt-0" type="radio" data-quiz-correct aria-label="Правильный вариант" @checked($option->is_correct)>
                                                <input class="form-control" type="text" data-quiz-field="option_text" name="quiz[questions][{{ $loop->parent->index }}][options][{{ $loop->index }}][option_text]" value="{{ $option->option_text }}" required>
                                                <button type="button" class="btn btn-link text-danger p-0" data-quiz-option-remove aria-label="Удалить вариант"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm px-0 mt-2" data-quiz-option-add><i class="bi bi-plus-lg me-1"></i>Добавить вариант</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>

<template id="quiz-question-template">
    <article class="admin-surface" data-sortable-item data-quiz-question>
        <div class="admin-surface__head">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-link text-muted p-0" data-drag-handle aria-label="Переместить вопрос"><i class="bi bi-grip-vertical fs-5"></i></button>
                <span class="small text-muted">Новый вопрос</span>
            </div>
            <button type="button" class="btn btn-link text-danger p-0" data-quiz-question-remove aria-label="Удалить вопрос"><i class="bi bi-trash3"></i></button>
        </div>
        <div class="admin-surface__body">
            <input type="hidden" data-quiz-field="id" value="">
            <div><label class="form-label">Вопрос</label><textarea class="form-control" rows="3" data-quiz-field="question_text" required></textarea></div>
            <div class="mt-3"><div class="small fw-semibold mb-2">Варианты ответа</div>
                <div class="admin-editor-stack" data-quiz-options>
                    @for($i = 0; $i < 2; $i++)
                        <div class="d-flex align-items-center gap-2" data-quiz-option>
                            <input type="hidden" data-quiz-field="id" value="">
                            <input type="hidden" data-quiz-correct-value data-quiz-field="is_correct" value="{{ $i === 0 ? '1' : '0' }}">
                            <input class="form-check-input mt-0" type="radio" data-quiz-correct aria-label="Правильный вариант" @checked($i === 0)>
                            <input class="form-control" type="text" data-quiz-field="option_text" required>
                            <button type="button" class="btn btn-link text-danger p-0" data-quiz-option-remove aria-label="Удалить вариант"><i class="bi bi-x-lg"></i></button>
                        </div>
                    @endfor
                </div>
                <button type="button" class="btn btn-link btn-sm px-0 mt-2" data-quiz-option-add><i class="bi bi-plus-lg me-1"></i>Добавить вариант</button>
            </div>
        </div>
    </article>
</template>
