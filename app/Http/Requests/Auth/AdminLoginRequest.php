<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class AdminLoginRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool { return true; }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array { return ['email'=>['required','email'],'password'=>['required','string']]; }
}
