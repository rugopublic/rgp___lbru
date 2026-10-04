<?php

namespace App\Http\Requests\User;

use App\Rules\EmailDomainAllowed;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UserRegistrationFormRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Since registration is available to all visitors,
        // authorization always returns true.
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method trims unnecessary whitespace characters
     * from the email field and ensures it's lowercase
     * before running the validation rules.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::of($this->input('email', ''))
                            ->lower()
                            ->trim()
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
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                new EmailDomainAllowed,
                'unique:users,email'
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email address',
        ];
    }
}
