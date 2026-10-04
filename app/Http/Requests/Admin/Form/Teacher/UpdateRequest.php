<?php
declare(strict_types=1);namespace App\Http\Requests\Admin\Form\Teacher;use App\Rules\Generic\FileUpload\CompletedFileUpload;use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;final class UpdateRequest extends FormRequest{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{$teacher=$this->route('teacher');return ['title'=>['sometimes','string','min:2','max:127',Rule::unique('teachers','title')->ignore($teacher?->id)],'motto'=>['sometimes','nullable','string','max:255'],'about'=>['sometimes','nullable','string','max:512'],'avatar'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],'avatar_remove'=>['sometimes','boolean']];}}
