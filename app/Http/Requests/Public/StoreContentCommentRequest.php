<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

final class StoreContentCommentRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }
}
