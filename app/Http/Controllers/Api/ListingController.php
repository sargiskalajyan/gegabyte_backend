<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\ListingUpdateRequest;
use App\Http\Resources\ListingResource;
use App\Models\Language;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ListingController extends Controller
{

    /**
     * @param ListingRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ListingRequest $request, $lang)
    {
        // Set locale
        $langModel = Language::where('code', $lang)->firstOrFail();
        app()->setLocale($langModel->code);

        $user = auth('api')->user();


        $userPackage = $user->activePackage();     // returns UserPackage
        $package = $userPackage->package;          // actual Package definition

        $maxListings = $package->max_active_listings;


        $activeCount = Listing::where('user_id', $user->id)->count();

        if ($activeCount >= $maxListings) {
            return response()->json([
                'message' => __('listings.limit_reached'),
                'limit'   => $maxListings
            ], 403);
        }

        // Create listing record
        $listing = Listing::create([
            'user_id'         => $user->id,
            'category_id'     => $request->category_id,
            'fuel_id'         => $request->fuel_id,
            'transmission_id' => $request->transmission_id,
            'drivetrain_id'   => $request->drivetrain_id,
            'condition_id'    => $request->condition_id,
            'location_id'     => $request->location_id,
//            'make_id'         => $request->make_id,
            'car_model_id'    => $request->car_model_id,
            'engine_id'       => $request->engine_id,
            'engine_size_id'  => $request->engine_size_id,
            'color_id'        => $request->color_id,
            'currency_id'     => $request->currency_id,
            'driver_type_id'  => $request->driver_type_id,

            'year'            => $request->year,
            'mileage'         => $request->mileage,
            'price'           => $request->price,
            'description'     => $request->description,
            'vin'             => $request->vin,
            'title'           => $request->title ?? null,
        ]);

        // Store photos (safe: only if provided)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $url = $this->processImageWithLogo($image);

                ListingPhoto::create([
                    'listing_id' => $listing->id,
                    'url'        => $url,
                ]);
            }
        }

        // Reload with required relations ONLY
        // (NOT translations — those are returned via JOINs in ListingResource)
        $listing->load([
            'photos',
            'user',
            'location',
            'category',
        ]);

        // Attach translation aliases (same as in SearchController)
        $listing->loadTranslationAttributes();

        return response()->json([
            'message' => __('listings.created_success'),
            'listing' => new ListingResource($listing),
        ], 201);
    }



    /**
     * @param $imageFile
     * @return string
     */
    private function processImage($imageFile): string
    {
        $manager = new ImageManager(new GdDriver());
        $image   = $manager->read($imageFile);

        // Resize proportionally without upscaling
        $image->scaleDown(1024, 1024);

        $ext = strtolower($imageFile->getClientOriginalExtension());

        $encoded = match ($ext) {
            'png' => $image->encode(new \Intervention\Image\Encoders\PngEncoder(false)),
            'jpg', 'jpeg' => $image->encode(new \Intervention\Image\Encoders\JpegEncoder(75)),
            'webp' => $image->encode(new \Intervention\Image\Encoders\WebpEncoder(75)),
            default => $image->encode(new \Intervention\Image\Encoders\JpegEncoder(75)),
        };

        // Save to storage/public/listings/
        $path = 'listings/' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($path, $encoded);

        return url('storage/' . $path);
    }


    /**
     * @param ListingUpdateRequest $request
     * @param Listing $listing
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ListingUpdateRequest $request, Listing $listing)
    {
        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Update listing fields
        $listing->update($request->validated());

        // If new images are uploaded → delete old ones & replace
        if ($request->hasFile('images')) {

            // Remove old images
            foreach ($listing->photos as $photo) {
                $this->deleteImageFile($photo->url);
                $photo->delete();
            }

            // Add new images
            foreach ($request->file('images') as $image) {
                $url = $this->processImage($image);
                $listing->photos()->create(['url' => $url]);
            }
        }

        return response()->json([
            'message' => __('listings.updated'),
            'listing' => $listing->load('photos'),
        ]);
    }


    /**
     * @param Listing $listing
     * @param ListingPhoto $photo
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePhoto(Listing $listing, ListingPhoto $photo)
    {
        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($photo->listing_id !== $listing->id) {
            return response()->json(['message' => 'Photo does not belong to this listing'], 422);
        }

        $this->deleteImageFile($photo->url);

        $photo->delete();

        return response()->json([
            'message' => __('listings.photo_deleted')
        ]);
    }

    /**
     * @param string $url
     * @return void
     */
    private function deleteImageFile(string $url): void
    {
        $path = str_replace(url('storage') . '/', '', $url);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }


    /**
     * @param $imageFile
     * @return string
     */
    private function processImageWithLogo($imageFile): string
    {
        $manager = new ImageManager(new GdDriver());
        $image   = $manager->read($imageFile);

        /**
         * ==================================
         * RESIZE to EXACT 600 × 500 (fit)
         * ==================================
         */
        $image->cover(600, 500); // best crop-fit method

        /**
         * ===========================
         * ADD WATERMARK / LOGO
         * ===========================
         */
        $watermarkPath = public_path('watermark/logo.jpg');

        if (file_exists($watermarkPath)) {
            $watermark = $manager->read($watermarkPath);

            // SCALE watermark to 15% of new image width
            $watermark->scaleDown(
                intval(600 * 0.15),
                intval(500 * 0.15)
            );

            // bottom-right with margin
            $image->place($watermark, 'top-right', 20, 20);
        }

        /**
         * ===========================
         * ENCODE ALWAYS AS WEBP
         * ===========================
         */
        $encoded = $image->encode(
            new \Intervention\Image\Encoders\WebpEncoder(75)
        );

        // Force extension
        $ext = "webp";

        // Save file
        $path = 'listings/' . uniqid('p_') . '.' . $ext;
        Storage::disk('public')->put($path, $encoded);

        return url('storage/' . $path);
    }




}
