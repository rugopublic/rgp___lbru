<div class="admin-aside-stack">
    <section class="admin-surface">
        <div class="admin-surface__head">
            <div>
                <h3 class="h6 mb-1">Связанный тест</h3>
                <div class="admin-section-note">Используется существующий <code>quizzes → questions → options</code> домен.</div>
            </div>
        </div>
        <div class="admin-surface__body">
            <label class="form-label" for="lesson-quiz-select">Quiz</label>
            <select class="form-select" name="quiz_id" id="lesson-quiz-select">
                <option value="">Без отдельного quiz</option>
                @foreach($lessonModel->quizzes as $attachedQuiz)
                    <option value="{{ $attachedQuiz->id }}" @selected($quiz?->id === $attachedQuiz->id)>{{ $attachedQuiz->title }}</option>
                @endforeach
            </select>
            <div class="admin-section-note mt-2">
                Редактор вопросов находится в основной области урока, поэтому одно и то же поле никогда не присутствует в форме дважды.
            </div>
        </div>
    </section>

    <section class="admin-surface">
        <div class="admin-surface__body">
            @if($quiz)
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle text-success fs-5"></i>
                    <div>
                        <strong>Quiz подключён</strong>
                        <div class="admin-section-note mt-1">Ответы и порядок вопросов сохраняются вместе с уроком одной транзакцией.</div>
                    </div>
                </div>
            @else
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle text-muted fs-5"></i>
                    <div>
                        <strong>Quiz пока не создан</strong>
                        <div class="admin-section-note mt-1">Включите переключатель «Использовать quiz» в основной области урока, чтобы создать или настроить тест.</div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
