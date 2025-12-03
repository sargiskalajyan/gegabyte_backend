<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\ListingUpdateRequest;
use App\Http\Resources\ListingResource;
use App\Models\Language;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{


    /**
     * @param ListingRequest $request
     * @param $lang
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ListingRequest $request, $lang, ImageService $images)
    {
        $langModel = Language::where('code', $lang)->firstOrFail();
        app()->setLocale($langModel->code);

        $user = auth('api')->user();

        $userPackage = $user->activePackage();
        $package     = $userPackage->package;

        $maxListings = $package->max_active_listings;
        $activeCount = Listing::where('user_id', $user->id)->count();

        if ($activeCount >= $maxListings) {
            return response()->json([
                'message' => __('listings.limit_reached'),
                'limit'   => $maxListings
            ], 403);
        }

        // Images saved before commit (we delete them manually if fail)
        $savedImages = [];

        try {
            DB::beginTransaction();

            // Create listing
            $listing = Listing::create([
                'user_id'         => $user->id,
                'category_id'     => $request->category_id,
                'fuel_id'         => $request->fuel_id,
                'transmission_id' => $request->transmission_id,
                'drivetrain_id'   => $request->drivetrain_id,
                'condition_id'    => $request->condition_id,
                'location_id'     => $request->location_id,
                'car_model_id'    => $request->car_model_id,
                'engine_id'       => $request->engine_id,
                'engine_size_id'  => $request->engine_size_id,
                'color_id'        => $request->color_id,
                'currency_id'     => $request->currency_id,
                'driver_type_id'  => $request->driver_type_id,
                'year'        => $request->year,
                'mileage'     => $request->mileage,
                'price'       => $request->price,
                'description' => $request->description,
                'vin'         => $request->vin,
                'title'       => $request->title,
            ]);

            /**
             * Save images
             */
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {

                    $processed = $images->processImageDual($imageFile);

                    // track paths for rollback
                    $savedImages[] = $processed['large'];
                    $savedImages[] = $processed['small'];

                    $listing->photos()->create([
                        'url'       => $processed['large'],
                        'thumbnail' => $processed['small'],
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            // Remove saved images from storage
            foreach ($savedImages as $img) {
                if (Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            }

            return response()->json([
                'message' =>  __('listings.error_creating'),
                'error'   => $e->getMessage()
            ], 500);
        }

        // Load relations for response
        $listing->load(['photos', 'user', 'location', 'category']);
        $listing->loadTranslationAttributes();

        return response()->json([
            'message' => __('listings.created_success'),
            'listing' => new ListingResource($listing),
        ], 201);
    }


    /**
     * @param ListingUpdateRequest $request
     * @param Listing $listing
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ListingUpdateRequest $request, Listing $listing, ImageService $images)
    {
        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        $newFilesSaved = [];
        $oldFilesDeleted = [];

        try {
            DB::beginTransaction();

            // Update fields
            $listing->update($request->validated());

            if ($request->hasFile('images')) {

                // delete old DB photo records but keep paths in array
                foreach ($listing->photos as $photo) {

                    $oldFilesDeleted[] = $photo->url;
                    $oldFilesDeleted[] = $photo->thumbnail;

                    $photo->delete();
                }

                // add new images
                foreach ($request->file('images') as $image) {

                    $result = $images->processImageDual($image);

                    $newFilesSaved[] = $result['large'];
                    $newFilesSaved[] = $result['small'];

                    $listing->photos()->create([
                        'url'       => $result['large'],
                        'thumbnail' => $result['small'],
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            // Delete newly created image files
            foreach ($newFilesSaved as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return response()->json([
                'message' => __('listings.error_updating'),
                'error'   => $e->getMessage()
            ], 500);
        }

        // Only after successful commit: delete old physical files
        foreach ($oldFilesDeleted as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
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
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePhoto(Listing $listing, ListingPhoto $photo, ImageService $images)
    {
        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        if ($photo->listing_id !== $listing->id) {
            return response()->json(['message' => __('listings.photo_not_owned')], 422);
        }

        $images->deleteImage($photo->url);
        $images->deleteImage($photo->thumbnail);

        $photo->delete();

        return response()->json([
            'message' => __('listings.photo_deleted'),
        ]);
    }

}
