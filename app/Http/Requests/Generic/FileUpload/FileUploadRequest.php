<?php

namespace App\Http\Requests\Generic\FileUpload;

use App\Rules\Generic\FileUpload\ValidFileUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file'
                => [
                    'required', 'file', 'max:5120'
                ],
            'upload_id'
                => [
                    'nullable',
                    'uuid',
                    app()->make(
                        ValidFileUpload::class,
                        ['owner' => $this->user()]
                    ),
                ],
            'chunk_number'
                => [
                    'nullable', 'numeric', 'integer', 'gte:1', 'lte:chunks_total'
                ],
            'chunks_total'
                => [
                    'nullable',
                    'required_with:chunk_number',
                    'numeric',
                    'integer',
                    'gte:1',
                    'max:' . max(1, (int) config('uploads.max_chunks', 1024)),
                ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            /*
            | Условие, которое не было      |  Сообщение о соответствующей ошибке, которое
            | выполнено при валидации       |  должен увидеть пользователь.
            */
            'file.required'                 => 'Отсутствует файл для загрузки.',
            'file.max'                      => 'Размер файла превышает :max КБ.',

            'upload_id.uuid'                => 'Некорректный идентификатор загрузки.',

            'chunk_number.numeric'          => 'Номер части файла должен быть числом.',
            'chunk_number.integer'          => 'Номер части файла должен быть целым числом.',
            'chunk_number.gte'              => 'Номер части файла должен быть не меньше единицы.',
            'chunk_number.lte'              => 'Номер части файла должен быть не больше общего числа частей.',

            'chunks_total.required_with'    => 'Не указано общее число частей файла.',
            'chunks_total.numeric'          => 'Общее число частей файла должно быть числом.',
            'chunks_total.integer'          => 'Общее число частей файла должно быть целым числом.',
            'chunks_total.gte'              => 'Общее число частей файла должно быть не меньше единицы.',
            'chunks_total.max'              => 'Общее число частей файла превышает допустимый предел.',
        ];
    }
}
