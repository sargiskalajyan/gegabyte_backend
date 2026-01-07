<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;;


class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver());
    }


    /**
     * @param $imageFile
     * @return string
     */
    public function processImage($imageFile): string
    {
        $image = $this->manager->read($imageFile);

        // Resize proportionally without upscaling
        $image->scaleDown(1024, 1024);

        $ext = strtolower($imageFile->getClientOriginalExtension());

        $encoded = match ($ext) {
            'png' => $image->encode(new PngEncoder()),
            'jpg', 'jpeg' => $image->encode(new JpegEncoder(quality: 75)),
            'webp' => $image->encode(new WebpEncoder(quality: 75)),
            default => $image->encode(new JpegEncoder(quality: 75)),
        };

        $path = 'listings/' . uniqid('img_') . '.' . $ext;
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }


    /**
     * @param $imageFile
     * @return string
     */
    public function processImageWithLogo($imageFile): string
    {
        $image = $this->manager->read($imageFile);

        // Fit (crop) to 600x500
        $image->cover(600, 500);

        // Watermark
        $watermarkPath = public_path('watermark/logo.jpg');

        if (file_exists($watermarkPath)) {

            $watermark = $this->manager->read($watermarkPath);

            // Make watermark ~15% of image width
            $watermark->scaleDown(
                intval(600 * 0.15),
                intval(500 * 0.15)
            );

            // Add watermark to top-right
            $image->place($watermark, 'top-right', 20, 20);
        }

        // Always encode WEBP
        $encoded = $image->encode(new WebpEncoder(quality: 75));

        $path = 'listings/' . uniqid('wm_') . '.webp';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }


    /**
     * @param $imageFile
     * @param string $method
     * @return string[]
     */
    public function processImageDualOld($imageFile, string $method = 'scaleDown'): array
    {
        // ------------------------------
        // Large image (1600px max width)
        // ------------------------------
//        $large = $this->manager->read($imageFile)
//            ->resize(1600, null, fn($c) => $c->aspectRatio())
//            ->encode(new WebpEncoder(quality: 90));

        $large = $this->manager->read($imageFile)
            ->scaleDown(1280, 1280)
            ->encode(new WebpEncoder(quality: 90));

        $largeName = 'listings/' . uniqid('lg_') . '.webp';
        Storage::disk('public')->put($largeName, $large);

        // ----------------------------------------
        // Small thumbnail — method selectable
        // ----------------------------------------
        $smallImage = $this->manager->read($imageFile);

        switch ($method) {
            case 'cover':
                $small = $smallImage->cover(400, 400);
                break;
            case 'scaleDown':
            default:
                $small = $smallImage->scaleDown(400, 400);
                break;
        }

        $small = $small->encode(new WebpEncoder(quality: 70));

        $smallName = 'listings/' . uniqid('sm_') . '.webp';
        Storage::disk('public')->put($smallName, $small);

        return [
            'large' => $largeName,
            'small' => $smallName,
        ];
    }


    public function processImageDual($imageFile, string $method = 'scaleDown'): array
    {
        $ratio = 4 / 3; // 4:3 aspect ratio

        // ------------------------------
        // Large image
        // ------------------------------
        $largeImg = $this->manager->read($imageFile);

        // Crop to 4:3 ratio
        if (intval($largeImg->width() / $ratio) > $largeImg->height()) {
            $largeImg->cover(intval($largeImg->height() * $ratio), $largeImg->height());
        } else {
            $largeImg->cover($largeImg->width(), intval($largeImg->width() / $ratio));
        }

        // Scale down if too large
        $largeImg->scaleDown(1280, 1280);

        $largeName = 'listings/' . uniqid('lg_') . '.webp';
        Storage::disk('public')->put($largeName, $largeImg->encode(new WebpEncoder(quality: 90)));

        // ------------------------------
        // Small image
        // ------------------------------
        $smallImg = $this->manager->read($imageFile);

        if (intval($smallImg->width() / $ratio) > $smallImg->height()) {
            $smallImg->cover(intval($smallImg->height() * $ratio), $smallImg->height());
        } else {
            $smallImg->cover($smallImg->width(), intval($smallImg->width() / $ratio));
        }

        switch ($method) {
            case 'cover':
                $smallImg->cover(400, 400);
                break;
            case 'scaleDown':
            default:
                $smallImg->scaleDown(400, 400);
                break;
        }

        $smallName = 'listings/' . uniqid('sm_') . '.webp';
        Storage::disk('public')->put($smallName, $smallImg->encode(new WebpEncoder(quality: 70)));

        return [
            'large' => $largeName,
            'small' => $smallName,
        ];
    }


    /**
     * @param $imageFile
     * @param string $method
     * @return string[]
     */
    public function processImageDual2($imageFile, string $method = 'scaleDown'): array
    {
        return [
            'large' => $this->makeVariant($imageFile, 1280, 960, 90, $method),
            'small' => $this->makeVariant($imageFile, 400, 300, 70, $method),
        ];
    }


    /**
     * @param $imageFile
     * @param int $w
     * @param int $h
     * @param int $quality
     * @param string $method
     * @return string
     */
    protected function makeVariant($imageFile, int $w, int $h, int $quality, string $method = 'scaleDown'): string
    {
        $image = $this->manager->read($imageFile)->orient();

        $ratio = $image->width() / $image->height();
        $targetRatio = $w / $h;

        if ($method === 'cover' || abs($ratio - $targetRatio) < 0.25) {
            // Crop to exact 4:3 if close or method=cover
            $image->cover($w, $h);
        } else {
            // Resize proportionally + pad to maintain 4:3
            $image->resize($w, $h, fn($c) => $c->aspectRatio());
            $image->pad($w, $h, '#f3f3f3', 'center');
        }

        $path = 'listings/' . uniqid('img_') . '.webp';
        Storage::disk('public')->put($path, $image->encode(new WebpEncoder(quality: $quality)));

        return $path;
    }




    /**
     * @param string|null $path
     * @return void
     */
    public function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        // Strip full URL → convert to relative path
        $path = str_replace(url('storage') . '/', '', $path);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
