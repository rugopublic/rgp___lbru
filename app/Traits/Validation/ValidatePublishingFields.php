<?php

namespace App\Traits\Validation;

use App\Enums\Material\PublishingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ValidatePublishingFields
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function publishingRules(): array
    {
        return [
            'publish_status'
                => [
                    'sometimes',
                    Rule::enum(PublishingStatus::class)
                ],

            'publish_date'
                => [
                    'nullable', 'date', 'required_if:publish_status,scheduled',
                    Rule::date()->after(now()->addHours(1))
                ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function publishingMessages(): array
    {
        return [
            'publish_status.enum'                => 'Неподдерживаемое значение статуса публикации.',

            'publish_date.date'                  => 'Неподдерживаемый формат даты публикации.',
            'publish_date.required_if'           => 'Не указана дата публикации.',
            'publish_date.after'                 => 'Дата публикации должна быть хотя бы на час позже текущего момента.'
        ];
    }
}
