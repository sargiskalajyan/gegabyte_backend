<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\ListingUpdateRequest;
use App\Http\Resources\ListingResource;
use App\Models\CarModel;
use App\Models\Language;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class  ListingController extends Controller
{

    /**
     * @param Request $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, $lang)
    {
        $langModel = Language::where('code', $lang)->firstOrFail();
        app()->setLocale($langModel->code);

        $user = auth('api')->user();

        $query = Listing::query()
            ->where('user_id', $user->id)
            ->with('photos');

        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->keyword}%")
                    ->orWhere('description', 'LIKE', "%{$request->keyword}%");
            });
        }

        $query->orderBy('created_at', 'DESC');

        $listings = $query->paginate($request->get('per_page', 20));

        // Load translations for each listing
        $listings->getCollection()->transform(function ($listing) {
            $listing->loadTranslationAttributes();
            return $listing;
        });

        return ListingResource::collection($listings);
    }



    /**
     * @param Request $request
     * @param $lang
     * @param $listingId
     * @return ListingResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $lang, $listingId)
    {
        $langModel = Language::where('code', $lang)->firstOrFail();
        app()->setLocale($langModel->code);

        $user = auth('api')->user();

        $listing = Listing::with('photos')
            ->where('user_id', $user->id)
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            return response()->json(['message' => __('listings.not_found')], 404);
        }

        $listing->loadTranslationAttributes();

        return new ListingResource($listing);
    }





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

        $makeId = null;
        if ($request->car_model_id) {
            $carModel = CarModel::find($request->car_model_id);
            if ($carModel) {
                $makeId = $carModel->make_id;
            }
        }

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
                'make_id'         => $makeId,
                'car_model_id'    => $request->car_model_id,
                'engine_id'       => $request->engine_id,
                'engine_size_id'  => $request->engine_size_id,
                'color_id'        => $request->color_id,
                'currency_id'     => $request->currency_id,
                'driver_type_id'  => $request->driver_type_id,

                'gas_equipment_id'      => $request->gas_equipment_id,
                'wheel_size_id'         => $request->wheel_size_id,
                'headlight_id'          => $request->headlight_id,
                'interior_color_id'     => $request->interior_color_id,
                'interior_material_id'  => $request->interior_material_id,
                'steering_wheel_id'     => $request->steering_wheel_id,


                'year'        => $request->year,
                'mileage'     => $request->mileage,
                'price'       => $request->price,
                'description' => $request->description,
                'vin'         => $request->vin,
                'exchange'    => $request->exchange ?? false,
                'published_until' => now()->addDays(30)->startOfDay(),
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

            // Load relations for response
            $listing->load(['photos', 'user', 'location', 'category']);
            $listing->loadTranslationAttributes();

            return response()->json([
                'message' => __('listings.created_success'),
                'listing' => new ListingResource($listing),
            ], 201);


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
    }



    /**
     * @param ListingUpdateRequest $request
     * @param $lang
     * @param Listing $listing
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ListingUpdateRequest $request, $lang, Listing $listing, ImageService $images)
    {

        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }


        if ($listing->status === 'pending') {
            return response()->json([
                'message' => __('listings.cannot_edit_listing')
            ], 403);
        }

        $newFilesSaved = [];
        $imageChanged = false;

        try {
            DB::beginTransaction();

            if ($request->car_model_id) {
                $carModel = CarModel::find($request->car_model_id);
                if ($carModel) {
                    $listing->make_id = $carModel->make_id;
                }
            }

            // Update fields
            $listing->update($request->validated());


            /**
             * 🖼 IMAGE UPLOAD
             */
            if ($request->hasFile('images')) {
                $imageChanged = true;

                $oldCount = $listing->photos()->count();
                $newCount = count($request->file('images'));

                // Enforce max 10 images
                if ($oldCount + $newCount > 10) {
                    return response()->json([
                        'message' => __('listings.max_images_exceeded'),
                        'limit' => 10,
                        'current' => $oldCount
                    ], 403);
                }

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

            if ($listing->status === 'published' && $imageChanged) {
                $listing->status = 'pending';
            }

            DB::commit();

            $listing->load(['photos', 'user', 'location', 'category']);
            $listing->loadTranslationAttributes();

            return response()->json([
                'message' => __('listings.updated'),
                'listing' => new ListingResource($listing),
            ]);

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
    }


    /**
     * @param Request $request
     * @param $lang
     * @param Listing $listing
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $lang, Listing $listing)
    {
        // Set locale dynamically
        app()->setLocale($lang);

        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json([
                'message' => __('listings.forbidden')
            ], 403);
        }

        $validStatuses = array_keys(__('listings.statuses', [], $lang));

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses)
        ]);

        $to = $request->status;

        $allowed = [
            'draft'     => ['pending'],
            'rejected'  => ['pending'],
            'expired'   => ['pending'],
            'published' => ['draft'],
        ];

        if (! isset($allowed[$listing->status]) || ! in_array($to, $allowed[$listing->status])) {
            return response()->json([
                'message' => __('listings.invalid_status_transition', [
                    'from' => __('listings.statuses.' . $listing->status),
                    'to' => __('listings.statuses.' . $to)
                ]),
                'from' => $listing->status,
                'to'   => $to,
            ], 422);
        }

        if (in_array($listing->status, ['expired', 'rejected']) && ! $user->canActivateListing()) {
            return response()->json([
                'message' => __('listings.package_limit_reached')
            ], 403);
        }

        $listing->status = $to;
        $listing->save();

        return response()->json([
            'message' => __('listings.status_updated'),
            'status'  => $listing->status,
        ]);
    }


    /**
     * @param $lang
     * @param Listing $listing
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTop($lang, Listing $listing)
    {
        app()->setLocale($lang);

        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        $userPackage = $user->activePackage();

        if (! $userPackage) {
            return response()->json([
                'message' => __('listings.top_limit_reached') ?? 'Top listings limit reached',
                'remaining' => 0,
            ], 403);
        }

        $userPackage->loadMissing('package');

        $days = (int)($userPackage->package->included_featured_days ?? 0);

        // If already top and not expired, don't consume another slot.
        if (
            $listing->is_top
            && (
                is_null($listing->top_expires_at)
                || $listing->top_expires_at->isFuture()
            )
        ) {
            return response()->json(['message' => __('listings.already_top') ?? 'Listing already top'], 200);
        }


        if (! $userPackage->exists) {
            return response()->json([
                'message' => __('listings.top_limit_reached') ?? 'Top listings limit reached',
                'remaining' => 0,
            ], 403);
        }




        try {
            DB::transaction(function () use ($user, $userPackage, $listing, $days) {
                $lockedPackage = $user->packages()
                    ->whereKey($userPackage->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedPackage->loadMissing('package');

                // If listing was top but expired (not yet cleaned), free its slot first.
                if (
                    $listing->is_top
                    && $listing->top_expires_at
                    && $listing->top_expires_at->isPast()
                ) {
                    $listing->is_top = false;
                    $listing->top_expires_at = null;
                    $listing->save();

                    if (($lockedPackage->used_top_listings ?? 0) > 0) {
                        $lockedPackage->decrement('used_top_listings', 1);
                    }
                }

                if ($lockedPackage->remainingTopListings() <= 0) {
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                        'message' => __('listings.top_limit_reached') ?? 'Top listings limit reached',
                        'remaining' => 0,
                    ], 403));
                }

                $listing->is_top = true;
                $listing->top_expires_at = $days > 0 ? now()->startOfDay()->addDays($days) : null;
                $listing->save();

                $lockedPackage->increment('used_top_listings');
            });
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            return $e->getResponse();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            // abort(response()) throws HttpException; let Laravel handle the response.
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => __('listings.error_updating'), 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => __('listings.marked_top') ?? 'Listing marked as top',
            'top_expires_at' => $listing->top_expires_at,
            'remaining' => $user->activePackage()->remainingTopListings(),
        ]);
    }





    /**
     * Delete listing + photos
     *
     * @param Listing $listing
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($lang, Listing $listing, ImageService $images)
    {
        $user = auth('api')->user();

        // User can delete only own listings
        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        DB::beginTransaction();

        try {
            // Collect physical file paths
            $filesToDelete = [];

            foreach ($listing->photos as $photo) {
                $filesToDelete[] = $photo->url;
                $filesToDelete[] = $photo->thumbnail;
                $photo->delete();
            }

            // Delete listing
            $listing->delete();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('listings.error_deleting'),
                'error'   => $e->getMessage(),
            ], 500);
        }

        // Delete physical images only after successful commit
        foreach ($filesToDelete as $file) {
            $images->deleteImage($file);
        }

        return response()->json([
            'message' => __('listings.deleted'),
        ]);
    }


    /**
     * @param $lang
     * @param Listing $listing
     * @param ListingPhoto $photo
     * @param ImageService $images
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePhoto($lang, Listing $listing, ListingPhoto $photo, ImageService $images)
    {
        $user = auth('api')->user();

        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        if ($photo->listing_id !== $listing->id) {
            return response()->json(['message' => __('listings.photo_not_owned')], 422);
        }

        $wasDefault = $photo->is_default;

        $images->deleteImage($photo->url);
        $images->deleteImage($photo->thumbnail);
        $photo->delete();

        /**
         * 🔁 published → pending if image affected
         */
        if ($listing->status === 'published' && $wasDefault) {
            $listing->status = 'pending';
            $listing->save();
        }

        return response()->json([
            'message' => __('listings.photo_deleted'),
        ]);
    }

}
