<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;

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
    public function processImageDual($imageFile, string $method = 'scaleDown'): array
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
