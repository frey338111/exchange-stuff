<?php

namespace App\Services;

use App\Models\ProductGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductGalleryImageService extends AbstractImageCreationService
{
    private const THUMBNAIL_WIDTH = 320;

    /**
     * @param  array<int, UploadedFile>  $images
     */
    public function store(int $productId, array $images, int $mainImageIndex): void
    {
        $storedPaths = [];

        try {
            foreach ($images as $index => $image) {
                $path = $this->storeImage($image, $productId);
                $storedPaths[] = $path;

                ProductGallery::create([
                    'product_id' => $productId,
                    'image_path' => $path,
                    'image_type' => $index === $mainImageIndex ? 'main' : 'other',
                ]);

                if ($index === $mainImageIndex) {
                    $thumbnailPath = $this->createThumbnail($path);
                    $storedPaths[] = $thumbnailPath;

                    ProductGallery::create([
                        'product_id' => $productId,
                        'image_path' => $thumbnailPath,
                        'image_type' => 'thumbnail',
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete($storedPaths);

            throw $throwable;
        }
    }

    private function storeImage(UploadedFile $image, int $productId): string
    {
        return $image->store("products/{$productId}/gallery", 'public');
    }

    private function createThumbnail(string $path): string
    {
        $thumbnailPath = $this->thumbnailPath($path);
        $this->createResizedImage($path, $thumbnailPath, self::THUMBNAIL_WIDTH, true);

        return $thumbnailPath;
    }

    private function thumbnailPath(string $path): string
    {
        return $this->suffixedPath($path, 'thumbnail');
    }
}
