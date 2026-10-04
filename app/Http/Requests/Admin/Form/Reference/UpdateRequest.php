<?php
declare(strict_types=1);
namespace App\Http\Requests\Admin\Form\Reference;
use App\Rules\Generic\FileUpload\CompletedFileUpload;use Illuminate\Foundation\Http\FormRequest;
final class UpdateRequest extends FormRequest{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return (bool)auth()->check();}

    /**
     * Normalizes request input before validation runs.
     * @return void
     */
    protected function prepareForValidation():void{if($this->input('is_visible')===''){$this->merge(['is_visible'=>false]);}}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{return ['title'=>['sometimes','string','min:2','max:255'],'category_id'=>['sometimes','nullable','exists:ingredient_categories,id'],'description'=>['sometimes','nullable','string','max:5000'],'body'=>['sometimes','nullable','string'],'cover'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],'cover_remove'=>['sometimes','boolean'],'protein'=>['sometimes','nullable','numeric','min:0'],'fat'=>['sometimes','nullable','numeric','min:0'],'carbs'=>['sometimes','nullable','numeric','min:0'],'kcal'=>['sometimes','nullable','numeric','min:0'],'default_grammage'=>['sometimes','nullable','numeric','min:0'],'storage_conditions'=>['sometimes','nullable','string','max:5000'],'selection_notes'=>['sometimes','nullable','string','max:5000'],'is_visible'=>['sometimes','boolean'],'product_ids'=>['sometimes','array'],'product_ids.*'=>['integer','distinct','exists:products,id'],'meta_title'=>['sometimes','nullable','string','max:255'],'meta_description'=>['sometimes','nullable','string','max:500']];}}
