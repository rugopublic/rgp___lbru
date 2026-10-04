<?php

namespace App\Http\Controllers\Generic\FileUpload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Generic\FileUpload\FileUploadRequest;
use App\Services\Admin\Generic\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * The file management service.
     *
     * @var FileService
     */
    private FileService $service;

    /**
     * Initializes the file upload controller instance and wires its required dependencies.
     *
     * @param FileService $service
     * @return void
     */
    public function __construct(FileService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the file upload.
     *
     * Validates the request via FileUploadRequest and delegates
     * the upload of the provided file to a service.
     *
     * The response is a JSON object with the `upload_id` and `result`
     * including the filepath and whether it is a partial.
     *
     * Any technical issues are logged for monitoring and debugging
     * purposes, without exposing details to the user.
     *
     * @param FileUploadRequest $request
     *
     * @return JsonResponse
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {
        $data = $request->safe()->collect();

        $uploadedFile   = $request->file('file');
        $uploadOwner    = $request->user();
        $uploadId       = $data->has('upload_id')       ? $data->get('upload_id')       : Str::uuid();
        $chunkNumber    = $data->has('chunk_number')    ? $data->get('chunk_number')    : 1;
        $chunksTotal    = $data->has('chunks_total')    ? $data->get('chunks_total')    : 1;

        $result = $this->service->upload(
            $uploadedFile,
            $uploadOwner,
            $uploadId,
            $chunkNumber,
            $chunksTotal
        );

        return response()->json([
           'upload_id'  => $uploadId,
           'result'     => $result
        ]);
    }
}
