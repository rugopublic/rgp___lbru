<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Recipe;

use Illuminate\Foundation\Http\FormRequest;

final class RecipeIngredientStoreRequest extends FormRequest
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
    public function rules(): array { return ['group_id'=>['required','integer'],'reference_item_id'=>['required','integer','exists:ingredients,id'],'quantity'=>['required','numeric','min:0'],'unit'=>['required','string','min:1','max:50']]; }
}
