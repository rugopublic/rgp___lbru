<?php

namespace App\Http\Requests\Auth\ForgotPassword;

use App\Rules\EmailDomainAllowed;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SendResetLinkRequest extends FormRequest
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
                new EmailDomainAllowed
            ],
        ];
    }
}
