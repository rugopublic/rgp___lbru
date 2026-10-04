<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        $routeCategory = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique($this->table(), 'name')->ignore($routeCategory, 'public_id'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Returns the database table used by this request or lookup.
     * @return string
     */
    public function table(): string
    {
        return match ($this->route('category_type')) {
            'article' => 'article_categories',
            'product' => 'product_categories',
            'recipe' => 'recipe_categories',
            'reference' => 'ingredient_categories',
            default => 'course_categories',
        };
    }
}
