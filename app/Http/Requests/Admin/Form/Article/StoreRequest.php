<?php
declare(strict_types=1);
namespace App\Http\Requests\Admin\Form\Article;
use Illuminate\Foundation\Http\FormRequest;
final class StoreRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return (bool)auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{return ['title'=>['required','string','min:3','max:255'],'author_id'=>['nullable','integer','exists:users,id']];}
}
