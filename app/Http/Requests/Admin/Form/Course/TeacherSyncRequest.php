<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Course;

use Illuminate\Foundation\Http\FormRequest;

final class TeacherSyncRequest extends FormRequest
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
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'distinct', 'exists:teachers,id'],
        ];
    }
}
