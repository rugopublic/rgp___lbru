<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

final class SearchRequest extends FormRequest
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
    public function rules(): array { return ['q'=>['nullable','string','max:120']]; }
}
