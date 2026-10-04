<?php
declare(strict_types=1);namespace App\Http\Requests\Admin\Form\User;use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;final class UpdateRequest extends FormRequest{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{$user=$this->route('user');return ['email'=>['sometimes','required','email',Rule::unique('users','email')->ignore($user?->id)],'name'=>['sometimes','required','string','min:2','max:255'],'password'=>['sometimes','nullable','string','min:12','confirmed'],'group_ids'=>['sometimes','array'],'group_ids.*'=>['integer','distinct','exists:groups,id']];}}
