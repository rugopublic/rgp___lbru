<?php

namespace App\Http\Requests\Admin\Form\Course;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255|unique:courses,title',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'                => 'Не заполнено название курса.',
            'title.min'                     => 'Название курса слишком короткое. Требуется название не короче :min символов.',
            'title.max'                     => 'Название курса слишком длинное. Требуется название не длиннее :max символов.',
            'title.unique'                  => 'Курс с таким названием уже существует.'
        ];
    }
}
