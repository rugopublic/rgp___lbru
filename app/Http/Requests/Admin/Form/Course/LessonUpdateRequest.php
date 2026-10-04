<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Course;

use App\Rules\Generic\FileUpload\CompletedFileUpload;
use Illuminate\Foundation\Http\FormRequest;

final class LessonUpdateRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach (['steps', 'quiz'] as $field) {
            $value = $this->input($field);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:2', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'is_preview' => ['sometimes', 'boolean'],
            'video' => ['sometimes', 'nullable', 'uuid', app()->make(CompletedFileUpload::class, ['owner' => $this->user()])],
            'video_remove' => ['sometimes', 'boolean'],
            'steps' => ['sometimes', 'array'],
            'steps.*.id' => ['nullable', 'integer', 'min:1'],
            'steps.*.type' => ['required', 'string', 'max:32'],
            'steps.*.title' => ['required', 'string', 'max:255'],
            'steps.*.content' => ['nullable', 'string'],
            'steps.*.video_url' => ['nullable', 'url', 'max:2048'],
            'steps.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
            'steps.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['sometimes', 'nullable', 'integer', 'exists:quizzes,id'],
            'quiz' => ['sometimes', 'nullable', 'array'],
            'quiz.enabled' => ['sometimes', 'boolean'],
            'quiz.title' => ['required_if:quiz.enabled,1', 'nullable', 'string', 'max:255'],
            'quiz.description' => ['nullable', 'string', 'max:5000'],
            'quiz.is_published' => ['sometimes', 'boolean'],
            'quiz.questions' => ['sometimes', 'array'],
            'quiz.questions.*.id' => ['nullable', 'integer', 'min:1'],
            'quiz.questions.*.question_text' => ['required', 'string', 'max:5000'],
            'quiz.questions.*.options' => ['required', 'array', 'min:2'],
            'quiz.questions.*.options.*.id' => ['nullable', 'integer', 'min:1'],
            'quiz.questions.*.options.*.option_text' => ['required', 'string', 'max:1000'],
            'quiz.questions.*.options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Adds post-validation checks to the validator.
     *
     * @param mixed $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!(bool) data_get($this->input('quiz', []), 'enabled', false)) {
                return;
            }

            foreach ((array) $this->input('quiz.questions', []) as $questionIndex => $question) {
                $correct = 0;
                foreach ((array) ($question['options'] ?? []) as $option) {
                    if (!empty($option['is_correct'])) {
                        $correct++;
                    }
                }
                if ($correct !== 1) {
                    $validator->errors()->add("quiz.questions.{$questionIndex}.options", 'У каждого вопроса должен быть ровно один правильный ответ.');
                }
            }

            if (empty($this->input('quiz.questions', [])) && (bool) $this->input('quiz.is_published', false)) {
                $validator->errors()->add('quiz.questions', 'Опубликованный quiz должен содержать хотя бы один вопрос.');
            }
        });
    }
}

