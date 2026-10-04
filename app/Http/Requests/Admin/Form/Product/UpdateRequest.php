<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Product;

use App\Rules\Generic\FileUpload\CompletedFileUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) auth()->check();
    }

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('is_visible') === '') {
            $this->merge(['is_visible' => false]);
        }
    }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        $item = $this->route('product');

        return [
            'title' => ['sometimes', 'string', 'min:2', 'max:255', Rule::unique('products', 'title')->ignore($item?->id)],
            'category_id' => ['sometimes', 'nullable', 'exists:product_categories,id'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'file' => ['sometimes', 'nullable', 'uuid', app()->make(CompletedFileUpload::class, ['owner' => $this->user()])],
            'file_remove' => ['sometimes', 'boolean'],
            'cover' => ['sometimes', 'nullable', 'uuid', app()->make(CompletedFileUpload::class, ['owner' => $this->user()])],
            'cover_remove' => ['sometimes', 'boolean'],
            'price' => ['sometimes', 'numeric', 'min:0.01', 'max:99999999.99'],
            'is_visible' => ['sometimes', 'boolean'],
            'article_ids' => ['sometimes', 'array'],
            'article_ids.*' => ['integer', 'distinct', 'exists:articles,id'],
            'recipe_ids' => ['sometimes', 'array'],
            'recipe_ids.*' => ['integer', 'distinct', 'exists:recipes,id'],
            'reference_item_ids' => ['sometimes', 'array'],
            'reference_item_ids.*' => ['integer', 'distinct', 'exists:ingredients,id'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Adds post-validation checks to the validator.
     *
     * @param mixed $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!$this->boolean('is_visible')) {
                return;
            }

            $product = $this->route('product');
            $currentFile = is_object($product) ? (string) $product->file : '';
            $newFile = trim((string) $this->input('file'));
            $remove = $this->boolean('file_remove');

            if (($currentFile === '' && $newFile === '') || $remove && $newFile === '') {
                $validator->errors()->add('file', 'Для продажи у товара должен быть доступный файл.');
            }
        });
    }
}
