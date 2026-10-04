<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Course;

use Illuminate\Foundation\Http\FormRequest;

final class StructureRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool { return auth()->check(); }

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $value = $this->input('structure');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) { $this->merge(['structure' => $decoded]); }
        }
    }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'structure' => ['required','array'],
            'structure.groups' => ['required','array'],
            'structure.groups.*.id' => ['required'],
            'structure.groups.*.label' => ['required','string','max:255'],
            'structure.groups.*.items' => ['array'],
            'structure.groups.*.items.*.value' => ['required','integer','distinct'],
        ];
    }
}
