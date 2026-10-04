<?php
declare(strict_types=1);namespace App\Http\Requests\Admin\Form\Teacher;use Illuminate\Foundation\Http\FormRequest;final class StoreRequest extends FormRequest{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{return ['title'=>['required','string','min:2','max:127'],'motto'=>['nullable','string','max:255']];}}
