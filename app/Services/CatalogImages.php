<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CatalogImages
{
    public function store(UploadedFile $file, int $factoryId): array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            $this->error();
        }
        $info = @getimagesize($file->getRealPath());
        if (! $info || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true) || $info[0] * $info[1] > config('catalog.image_pixels')) {
            $this->error();
        }
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $source) {
            $this->error();
        }
        $ratio = min(1, config('catalog.image_width') / max($info[0], $info[1]));
        $image = imagecreatetruecolor(max(1, (int) ($info[0] * $ratio)), max(1, (int) ($info[1] * $ratio)));
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagecopyresampled($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image), $info[0], $info[1]);
        imagedestroy($source);
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getRealPath());
            $orientation = (int) ($exif['Orientation'] ?? 1);
            if (in_array($orientation, [2, 5, 7], true)) {
                imageflip($image, IMG_FLIP_HORIZONTAL);
            } elseif ($orientation === 4) {
                imageflip($image, IMG_FLIP_VERTICAL);
            }
            $angle = [3 => 180, 5 => 90, 6 => -90, 7 => -90, 8 => 90][$orientation] ?? 0;
            if ($angle) {
                $rotated = imagerotate($image, $angle, 0);
                if ($rotated) {
                    imagedestroy($image);
                    $image = $rotated;
                }
            }
        }
        try {
            foreach ([78, 65, 50] as $quality) {
                ob_start();
                imagewebp($image, null, $quality);
                $bytes = ob_get_clean();
                if (strlen($bytes) <= config('catalog.image_bytes')) {
                    break;
                }
            }
            if (! $bytes || strlen($bytes) > config('catalog.image_bytes')) {
                $this->error();
            }
            $path = 'catalog/'.$factoryId.'/'.Str::uuid().'.webp';
            if (! Storage::disk('local')->put($path, $bytes)) {
                $this->error();
            }

            return ['path' => $path, 'bytes' => strlen($bytes)];
        } finally {
            imagedestroy($image);
        }
    }

    private function error(): void
    {
        throw ValidationException::withMessages(['photos' => __('catalog.image_error')]);
    }
}
