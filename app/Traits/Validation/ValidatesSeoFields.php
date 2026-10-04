<?php

namespace App\Traits\Validation;

use Illuminate\Contracts\Validation\ValidationRule;

trait ValidatesSeoFields
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function seoRules(): array
    {
        return [
            'meta_title'
                => [
                    'sometimes', 'nullable', 'string', 'min:5', 'max:255'
                ],

            'meta_description'
                => [
                    'sometimes', 'nullable', 'string', 'min:5', 'max:500'
                ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function seoMessages(): array
    {
        return [
            'meta_title.min'                => 'Название для SEO слишком короткое. Требуется название не короче :min символов.',
            'meta_title.max'                => 'Название для SEO слишком длинное. Требуется название не длиннее :max символов.',

            'meta_description.min'          => 'Описание для SEO слишком короткое. Требуется описание не короче :min символов.',
            'meta_description.max'          => 'Описание для SEO слишком длинное. Требуется описание не длиннее :max символов.',
        ];
    }
}
