<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProductFileController extends Controller
{

    /**
     * Initializes the product file controller instance and wires its required dependencies.
     *
     * @param ProductRepositoryInterface $products
     * @param ProductPurchaseRepositoryInterface $purchases
     * @return void
     */
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPurchaseRepositoryInterface $purchases,
    ) {}

    /**
     * Handles the download HTTP action for the product file controller resource.
     *
     * @param string $product
     * @return BinaryFileResponse
     */
    public function download(string $product): BinaryFileResponse
    {
        $item = $this->products->findForAccess($product);
        abort_unless($item, 404);

        $purchase = $this->purchases->findActiveForUser((int) auth()->id(), (int) $item->id);
        abort_unless($purchase, 403);
        abort_if(blank($item->file), 404);

        $disk = Storage::disk((string) config('files.product_disk', 'local'));
        $path = str_contains((string) $item->file, '/')
            ? (string) $item->file
            : $item->mediaFolder() . '/' . $item->file;

        if (!$disk->exists($path)) {
            throw new NotFoundHttpException('Файл товара недоступен.');
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return response()->download(
            $disk->path($path),
            $filename,
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }
}
