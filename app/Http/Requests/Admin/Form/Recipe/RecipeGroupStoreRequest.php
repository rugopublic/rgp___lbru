<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Recipe;

use Illuminate\Foundation\Http\FormRequest;

final class RecipeGroupStoreRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool { return auth()->check(); }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array { return ['title'=>['required','string','min:1','max:255']]; }
}
