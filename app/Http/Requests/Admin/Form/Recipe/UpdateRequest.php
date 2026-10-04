<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Form\Recipe;

use App\Rules\Generic\FileUpload\CompletedFileUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRequest extends FormRequest
{

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $structure = $this->input('structure');
        if (is_string($structure)) {
            $decoded = json_decode($structure, true);
            if (json_last_error() === JSON_ERROR_NONE) { $structure = $decoded; }
        }
        if (is_array($structure)) {
            $structure['groups'] = array_map(static function (array $group): array {
                return [
                    'id' => $group['id'] ?? null,
                    'title' => $group['label'] ?? $group['title'] ?? '',
                    'items' => array_map(static function (array $item): array {
                        return [
                            'id' => $item['id'] ?? null,
                            'reference_item_id' => $item['value'] ?? $item['reference_item_id'] ?? null,
                            'quantity' => data_get($item, 'data.quantity'),
                            'unit' => data_get($item, 'data.unit'),
                        ];
                    }, is_array($group['items'] ?? null) ? $group['items'] : []),
                ];
            }, is_array($structure['groups'] ?? null) ? $structure['groups'] : []);
            $this->merge(['structure'=>$structure]);
        }

        foreach (['is_visible'] as $field) {
            if ($this->input($field) === '') { $this->merge([$field => false]); }
        }
    }

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize(): bool { return auth()->check(); }

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'=>['sometimes','string','min:2','max:255'],
            'description'=>['sometimes','nullable','string','max:5000'],
            'body'=>['sometimes','nullable','string'],
            'author_id'=>['sometimes','nullable','exists:users,id'],
            'category_ids'=>['sometimes','array'],
            'category_ids.*'=>['integer','distinct','exists:recipe_categories,id'],
            'cover'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],
            'cover_remove'=>['sometimes','boolean'],
            'video'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],
            'video_remove'=>['sometimes','boolean'],
            'visibility_status'=>['sometimes',Rule::in(['draft','demo','published'])],
            'publish_at'=>['sometimes','nullable','date'],
            'product_ids'=>['sometimes','array'],
            'product_ids.*'=>['integer','distinct','exists:products,id'],
            'meta_title'=>['sometimes','nullable','string','max:255'],
            'meta_description'=>['sometimes','nullable','string','max:255'],
            'structure'=>['sometimes','nullable','array'],
            'structure.groups'=>['sometimes','array'],
            'structure.groups.*.id'=>['nullable','integer'],
            'structure.groups.*.title'=>['required','string','max:255'],
            'structure.groups.*.items'=>['sometimes','array'],
            'structure.groups.*.items.*.id'=>['nullable','integer'],
            'structure.groups.*.items.*.reference_item_id'=>['required','integer','exists:ingredients,id'],
            'structure.groups.*.items.*.quantity'=>['required','numeric','min:0'],
            'structure.groups.*.items.*.unit'=>['required','string','max:50'],
            'steps'=>['sometimes','array'],
            'steps.*.id'=>['nullable','integer','min:1'],
            'steps.*.description'=>['required','string','max:20000'],
            'steps.*.duration_seconds'=>['nullable','integer','min:0','max:86400'],
            'steps.*.image'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],
            'steps.*.image_remove'=>['sometimes','boolean'],
        ];
    }
}
