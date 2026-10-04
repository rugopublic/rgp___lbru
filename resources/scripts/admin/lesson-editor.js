/**
 * @file resources/scripts/admin/lesson-editor.js
 * @description Admin shared module for resources/scripts/admin/lesson-editor.js; provides reusable or page-level administration behavior.
 */

function renumberSteps(root) {
    root.querySelectorAll('[data-lesson-step]').forEach((item, index) => {
        item.querySelectorAll('[data-field]').forEach((field) => {
            const key = field.dataset.field;
            field.name = `steps[${index}][${key}]`;
        });

        const order = item.querySelector('[data-step-order]');
        if (order) {
            order.name = `steps[${index}][sort_order]`;
            order.value = String(index);
        }
    });
}

function syncQuizCorrectness(question) {
    const options = Array.from(question.querySelectorAll('[data-quiz-option]'));
    const checkedIndex = options.findIndex((option) => option.querySelector('[data-quiz-correct]')?.checked);

    options.forEach((option, index) => {
        const radio = option.querySelector('[data-quiz-correct]');
        const hidden = option.querySelector('[data-quiz-correct-value]');
        if (radio) radio.name = `quiz[questions][${question.dataset.quizIndex}][correct]`;
        if (hidden) hidden.value = index === checkedIndex ? '1' : '0';
    });
}

function renumberQuiz(root) {
    root.querySelectorAll('[data-quiz-question]').forEach((question, questionIndex) => {
        question.dataset.quizIndex = String(questionIndex);

        question.querySelectorAll('[data-quiz-field]').forEach((field) => {
            const key = field.dataset.quizField;
            if (key === 'question_text') {
                field.name = `quiz[questions][${questionIndex}][question_text]`;
            } else if (key === 'id' && field.closest('[data-quiz-question]') && !field.closest('[data-quiz-option]')) {
                field.name = `quiz[questions][${questionIndex}][id]`;
            }
        });

        question.querySelectorAll('[data-quiz-option]').forEach((option, optionIndex) => {
            option.querySelectorAll('[data-quiz-field]').forEach((field) => {
                const key = field.dataset.quizField;
                field.name = `quiz[questions][${questionIndex}][options][${optionIndex}][${key}]`;
            });
        });

        syncQuizCorrectness(question);
    });
}

function notifyFormChange(element) {
    element?.dispatchEvent(new Event('change', { bubbles: true }));
}

document.addEventListener('click', (event) => {
    const stepAdd = event.target.closest('[data-lesson-step-add]');
    const stepRemove = event.target.closest('[data-lesson-step-remove]');
    const questionAdd = event.target.closest('[data-quiz-question-add]');
    const questionRemove = event.target.closest('[data-quiz-question-remove]');
    const optionAdd = event.target.closest('[data-quiz-option-add]');
    const optionRemove = event.target.closest('[data-quiz-option-remove]');

    if (stepAdd) {
        const root = document.querySelector('#lesson-steps');
        const template = document.querySelector('#lesson-step-template');
        if (!root || !template) return;
        root.append(template.content.cloneNode(true));
        renumberSteps(root);
        notifyFormChange(root);
    }

    if (stepRemove) {
        const root = stepRemove.closest('#lesson-steps');
        stepRemove.closest('[data-lesson-step]')?.remove();
        if (root) {
            renumberSteps(root);
            notifyFormChange(root);
        }
    }

    if (questionAdd) {
        const root = questionAdd.closest('[data-lesson-quiz]')?.querySelector('[data-quiz-questions]');
        const template = document.querySelector('#quiz-question-template');
        if (!root || !template) return;
        root.append(template.content.cloneNode(true));
        renumberQuiz(root);
        notifyFormChange(root);
    }

    if (questionRemove) {
        const root = questionRemove.closest('[data-quiz-questions]');
        questionRemove.closest('[data-quiz-question]')?.remove();
        if (root) {
            renumberQuiz(root);
            notifyFormChange(root);
        }
    }

    if (optionAdd) {
        const question = optionAdd.closest('[data-quiz-question]');
        const options = question?.querySelector('[data-quiz-options]');
        const first = options?.querySelector('[data-quiz-option]');
        if (!question || !options || !first) return;
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input').forEach((input) => {
            if (input.type === 'text') input.value = '';
            if (input.type === 'radio') input.checked = false;
            if (input.type === 'hidden') input.value = '';
        });
        options.append(clone);
        renumberQuiz(question.closest('[data-quiz-questions]'));
        notifyFormChange(options);
    }

    if (optionRemove) {
        const question = optionRemove.closest('[data-quiz-question]');
        const options = question?.querySelector('[data-quiz-options]');
        if (question && options && options.children.length > 2) {
            optionRemove.closest('[data-quiz-option]')?.remove();
            renumberQuiz(question.closest('[data-quiz-questions]'));
            notifyFormChange(options);
        }
    }
});

document.addEventListener('change', (event) => {
    const enabled = event.target.closest('#lesson-quiz-enabled');
    if (enabled) {
        const editor = document.querySelector('[data-quiz-editor]');
        editor?.classList.toggle('d-none', !enabled.checked);
        notifyFormChange(enabled);
        return;
    }

    const correct = event.target.closest('[data-quiz-correct]');
    if (!correct) return;
    const question = correct.closest('[data-quiz-question]');
    if (question) {
        syncQuizCorrectness(question);
        notifyFormChange(question);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelector('#lesson-steps');
    if (steps) renumberSteps(steps);

    const quiz = document.querySelector('[data-quiz-questions]');
    if (quiz) renumberQuiz(quiz);
});
