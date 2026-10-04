<?php
declare(strict_types=1);namespace App\Http\Requests\Admin\Form\User;use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;final class StoreRequest extends FormRequest{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{return ['email'=>['required','email',Rule::unique('users','email')],'name'=>['required','string','min:2','max:255'],'password'=>['required','string','min:12','confirmed'],'group_ids'=>['sometimes','array'],'group_ids.*'=>['integer','distinct','exists:groups,id']];}}
