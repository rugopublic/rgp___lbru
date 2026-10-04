<?php

declare(strict_types=1);
namespace App\Http\Requests\Admin\Form\Course;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Course\CourseDifficulty;
use App\Enums\Material\PublishingStatus;
use App\Rules\Generic\FileUpload\CompletedFileUpload;
final class UpdateRequest extends FormRequest
{

    /**
     * Determines whether the current request is authorized.
     * @return bool
     */
    public function authorize():bool{return auth()->check();}

    /**
     * Returns the validation rules applied to the request.
     * @return array
     */
    public function rules():array{ $course=$this->route('course'); return [
 'title'=>['sometimes','string','min:3','max:127',Rule::unique('courses','title')->ignore($course?->id)],
 'category_id'=>['sometimes','nullable',Rule::exists('course_categories','id')],
 'difficulty'=>['sometimes',Rule::enum(CourseDifficulty::class)],
 'summary'=>['sometimes','nullable','string','max:512'],'pitch'=>['sometimes','nullable','string'],
 'objective'=>['sometimes','nullable','string','max:10000'],'skills'=>['sometimes','nullable','string','max:10000'],
 'audience'=>['sometimes','nullable','string','max:1000'],'features'=>['sometimes','nullable','string','max:1000'],
 'prerequisites'=>['sometimes','nullable','string','max:1000'],'preferables'=>['sometimes','nullable','string','max:1000'],
 'cover'=>['sometimes','nullable','uuid',app()->make(CompletedFileUpload::class,['owner'=>$this->user()])],
 'cover_remove'=>['sometimes','boolean'],'price'=>['sometimes','numeric','min:0'],
 'publish_status'=>['sometimes',Rule::enum(PublishingStatus::class)],
 'publish_date'=>['sometimes','nullable','date','required_if:publish_status,scheduled'],
 'meta_title'=>['sometimes','nullable','string','max:255'],'meta_description'=>['sometimes','nullable','string','max:500'],
 ]; }
}
