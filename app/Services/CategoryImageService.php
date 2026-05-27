<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryImageService extends AbstractImageCreationService
{
    private const MOBILE_WIDTH = 768;

    public function store(UploadedFile $file, ?string $existingPath = null): string
    {
        $this->delete($existingPath);

        $path = $file->store('categories', 'public');
        $this->createMobileVariant($path);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete([
            $path,
            $this->mobilePath($path),
        ]);
    }

    public function mobileUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($this->mobilePath($path))) {
            return null;
        }

        return Storage::disk('public')->url($this->mobilePath($path));
    }

    private function createMobileVariant(string $path): void
    {
        $this->createResizedImage($path, $this->mobilePath($path), self::MOBILE_WIDTH);
    }

    private function mobilePath(string $path): string
    {
        return $this->suffixedPath($path, 'mobile');
    }
}
