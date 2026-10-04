<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guest();
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
     * We deliberately avoid checking whether the association
     * of email to user exists in order to prevent account
     * enumeration attacks.
     *
     * We deliberately avoid the EmailDomainAllowed rule
     * while checking the email because invalid emails
     * cannot be registered or changed to, therefore
     * they never exist in the database.
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
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255'
            ],
            'remember' => [
                'sometimes',
                'boolean'
            ],
            'redirect' => [
                'sometimes',
                'nullable',
                'string',
                'max:2048',
            ]
        ];
    }
}
