<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProductMediaController extends Controller
{

    /**
     * Initializes the product media controller instance and wires its required dependencies.
     *
     * @param ProductRepositoryInterface $products
     * @return void
     */
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    /**
     * Handles the file HTTP action for the product media controller resource.
     *
     * @param string $product
     * @return BinaryFileResponse
     */
    public function file(string $product): BinaryFileResponse
    {
        $item = $this->products->findForEdit($product);
        abort_if(blank($item->file), 404);

        $disk = Storage::disk((string) config('files.product_disk', 'local'));
        $path = str_contains((string) $item->file, '/')
            ? (string) $item->file
            : $item->mediaFolder() . '/' . $item->file;

        if (!$disk->exists($path)) {
            // Before the migration command is run, legacy files may still be on the public disk.
            $legacy = Storage::disk('public');
            if ($legacy->exists($path)) {
                $disk = $legacy;
            } else {
                throw new NotFoundHttpException('Файл товара недоступен.');
            }
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $basename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $safeTitle = trim((string) Str::slug((string) $item->title, '-')) ?: 'product-file';
        $downloadName = $safeTitle . ($extension !== '' ? '.' . strtolower($extension) : '');

        return response()->download(
            $disk->path($path),
            $downloadName,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="' . addcslashes($downloadName, '"\\') . '"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }
}
