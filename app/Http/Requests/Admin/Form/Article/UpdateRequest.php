<?php
declare(strict_types=1);
namespace App\Http\Requests\Admin\Form\Article;
use App\Rules\Generic\FileUpload\CompletedFileUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class UpdateRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return (bool)auth()->check();}

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    public function prepareForValidation():void{foreach(['is_visible'] as $field){if($this->input($field)===''){$this->merge([$field=>false]);}}}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{$article=$this->route('article');return ['title'=>['sometimes','string','min:3','max:255',Rule::unique('articles','title')->ignore($article?->id)],'category_id'=>['sometimes','nullable','exists:article_categories,id'],'author_id'=>['sometimes','nullable','exists:users,id'],'description'=>['sometimes','nullable','string','max:2000'],'body'=>['sometimes','nullable','string'],'cover'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],'cover_remove'=>['sometimes','boolean'],'is_visible'=>['sometimes','boolean'],'publish_at'=>['sometimes','nullable','date'],'product_ids'=>['sometimes','array'],'product_ids.*'=>['integer','distinct','exists:products,id'],'meta_title'=>['sometimes','nullable','string','max:255'],'meta_description'=>['sometimes','nullable','string','max:500']];}
}
