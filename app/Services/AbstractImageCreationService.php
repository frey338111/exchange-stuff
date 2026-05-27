<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class AbstractImageCreationService
{
    protected function createResizedImage(
        string $path,
        string $targetPath,
        int $maxWidth,
        bool $copyOriginalOnFailure = false
    ): void {
        $sourcePath = Storage::disk('public')->path($path);
        $destinationPath = Storage::disk('public')->path($targetPath);

        if (class_exists(\Imagick::class)) {
            $this->createWithImagick($sourcePath, $destinationPath, $maxWidth);

            return;
        }

        if (function_exists('imagecreatetruecolor') && $this->createWithGd($sourcePath, $destinationPath, $maxWidth)) {
            return;
        }

        if ($copyOriginalOnFailure) {
            Storage::disk('public')->copy($path, $targetPath);
        }
    }

    protected function suffixedPath(string $path, string $suffix): string
    {
        $directory = Str::contains($path, '/') ? Str::beforeLast($path, '/') : '';
        $filename = Str::afterLast($path, '/');
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        return trim("{$directory}/{$name}-{$suffix}.{$extension}", '/');
    }

    private function createWithImagick(string $sourcePath, string $targetPath, int $maxWidth): void
    {
        $image = new \Imagick($sourcePath);
        $width = $image->getImageWidth();

        if ($width > $maxWidth) {
            $image->thumbnailImage($maxWidth, 0);
        }

        $image->writeImage($targetPath);
        $image->clear();
    }

    private function createWithGd(string $sourcePath, string $targetPath, int $maxWidth): bool
    {
        $info = getimagesize($sourcePath);

        if (! $info) {
            return false;
        }

        [$sourceWidth, $sourceHeight] = $info;
        $targetWidth = min($maxWidth, $sourceWidth);
        $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));
        $source = $this->createGdImage($sourcePath, $info['mime'] ?? '');

        if (! $source) {
            return false;
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($info['mime'] ?? '', ['image/png', 'image/webp'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
        }

        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        $this->writeGdImage($target, $targetPath, $info['mime'] ?? '');

        imagedestroy($source);
        imagedestroy($target);

        return true;
    }

    private function createGdImage(string $sourcePath, string $mime): mixed
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };
    }

    private function writeGdImage(mixed $image, string $targetPath, string $mime): void
    {
        match ($mime) {
            'image/jpeg' => imagejpeg($image, $targetPath, 82),
            'image/png' => imagepng($image, $targetPath, 8),
            'image/gif' => imagegif($image, $targetPath),
            'image/webp' => imagewebp($image, $targetPath, 82),
            default => null,
        };
    }
}
