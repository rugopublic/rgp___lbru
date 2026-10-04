<?php

namespace App\Http\Requests\Auth\ForgotPassword;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'password' => $this->p_input
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the password and its confirmation, as well as the token
     * field required to identify which user’s password should be reset.
     *
     * Uses custom field naming (`p_input` / `p_repeat`) to avoid
     * revealing framework conventions.
     *
     * Note: This FormRequest does not validate the token itself.
     * Token existence, expiration, and consumption are handled
     * securely in the service layer. This ensures that no sensitive
     * information about user accounts is exposed during validation.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'p_input' => ['required', 'string', 'min:8', 'max:255', 'same:p_repeat'],
            'p_repeat' => ['required', 'string', 'min:8', 'max:255'],
            'token' => ['required', 'string']
        ];
    }
}
