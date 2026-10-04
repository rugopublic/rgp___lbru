<?php

namespace App\Http\Requests\Public;

use App\Services\Cookie\CookieConsentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCookieConsentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'required'      => $this->has('required')
                                    ? (bool) $this->input('required')
                                    : CookieConsentService::DEFAULT_REQUIRED,
            'functional'    => $this->has('functional')
                                    ? (bool) $this->input('functional')
                                    : CookieConsentService::DEFAULT_FUNCTIONAL,
            'marketing'     => $this->has('marketing')
                                    ? (bool) $this->input('marketing')
                                    : CookieConsentService::DEFAULT_MARKETING,
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
            'required'   => 'sometimes|accepted',
            'functional' => 'sometimes|boolean|nullable',
            'marketing'  => 'sometimes|boolean|nullable',
        ];
    }
}
