<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UserAccountUpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     *
     * This method trims unnecessary whitespace characters
     * from the name field and ensures it's title case (as all
     * names should be) before running the validation rules.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $name = $this->input('name', null);

        if($name === null) {
            return;
        }

        $this->merge([
            'name' => Str::of($name)
                ->squish()
                ->title()
                ->toString()
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:254'],
        ];
    }
}
