<?php

namespace App\Rules\Generic\FileUpload;

use App\Models\User;
use App\Services\Admin\Generic\FileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class CompletedFileUpload implements ValidationRule
{
    /**
     * The file management service.
     *
     * @var FileService
     */
    private FileService $service;

    /**
     * The owner of this upload.
     *
     * @var User
     */
    private User $owner;

    /**
     * Initializes the completed file upload instance and wires its required dependencies.
     *
     * @param User $owner
     * @param FileService $service
     * @return void
     */
    public function __construct(User $owner, FileService $service)
    {
        $this->service = $service;
        $this->owner   = $owner;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute Field name
     * @param mixed $value Field value
     * @param Closure $fail Callback to trigger when validation fails.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!Str::isUuid($value)) {
            $fail('validation.uuid');
        } elseif(!$this->service->ready($this->owner, $value)) {
            $fail('validation.completed_upload');
        }
    }
}
