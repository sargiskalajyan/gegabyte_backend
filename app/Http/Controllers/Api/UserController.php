<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{


    /**
     * @param Request $request
     * @param $lang
     * @param $userId
     * @return UserProfileResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $lang, $userId)
    {
        $user = User::query()
            ->select([
                'users.*',
                'loc_trans.name AS location_name',
            ])
            ->leftJoin('locations', 'locations.id', '=', 'users.location_id')
            ->leftJoin('location_translations AS loc_trans', function ($join) use ($lang) {
                $join->on('loc_trans.location_id', '=', 'locations.id')
                    ->leftJoin('languages AS l_loc', 'l_loc.id', '=', 'loc_trans.language_id')
                    ->where('l_loc.code', $lang);
            })
            ->with(['language'])
            ->withCount([
                'listings' => fn ($q) => $q->where('status', 'published')
            ])
            ->where('users.id', $userId)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => __('auth.user_not_found'),
            ], 404);
        }
        return new UserProfileResource($user);
    }



    /**
     * @param Request $request
     * @param $lang
     * @param $userId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function userListings(Request $request, $lang, $userId)
    {
        $perPage = $request->integer('per_page', 20);

        $query = Listing::query()
            ->select([
                'listings.*',
                'make_trans.name AS make_name',
                'model_trans.name AS model_name',
                'fuel_trans.name AS fuel_name',
                'trans_trans.name AS transmission_name',
                'drive_trans.name AS drivetrain_name',
                'cond_trans.name AS condition_name',
                'color_trans.name AS color_name',
                'driver_trans.name AS driver_type_name',
                'cat_trans.name AS category_name',
                'loc_trans.name AS location_name',
            ])

            // ------------------ TRANSLATION JOINS ------------------
            ->leftJoin('make_translations AS make_trans', function ($join) use ($lang) {
                $join->on('make_trans.make_id', '=', 'listings.make_id')
                    ->leftJoin('languages AS l_make', 'l_make.id', '=', 'make_trans.language_id')
                    ->where('l_make.code', $lang);
            })

            ->leftJoin('car_model_translations AS model_trans', function ($join) use ($lang) {
                $join->on('model_trans.car_model_id', '=', 'listings.car_model_id')
                    ->leftJoin('languages AS l_model', 'l_model.id', '=', 'model_trans.language_id')
                    ->where('l_model.code', $lang);
            })

            ->leftJoin('fuel_translations AS fuel_trans', function ($join) use ($lang) {
                $join->on('fuel_trans.fuel_id', '=', 'listings.fuel_id')
                    ->leftJoin('languages AS l_fuel', 'l_fuel.id', '=', 'fuel_trans.language_id')
                    ->where('l_fuel.code', $lang);
            })

            ->leftJoin('transmission_translations AS trans_trans', function ($join) use ($lang) {
                $join->on('trans_trans.transmission_id', '=', 'listings.transmission_id')
                    ->leftJoin('languages AS l_trans', 'l_trans.id', '=', 'trans_trans.language_id')
                    ->where('l_trans.code', $lang);
            })

            ->leftJoin('drivetrain_translations AS drive_trans', function ($join) use ($lang) {
                $join->on('drive_trans.drivetrain_id', '=', 'listings.drivetrain_id')
                    ->leftJoin('languages AS l_drive', 'l_drive.id', '=', 'drive_trans.language_id')
                    ->where('l_drive.code', $lang);
            })

            ->leftJoin('condition_translations AS cond_trans', function ($join) use ($lang) {
                $join->on('cond_trans.condition_id', '=', 'listings.condition_id')
                    ->leftJoin('languages AS l_cond', 'l_cond.id', '=', 'cond_trans.language_id')
                    ->where('l_cond.code', $lang);
            })

            ->leftJoin('color_translations AS color_trans', function ($join) use ($lang) {
                $join->on('color_trans.color_id', '=', 'listings.color_id')
                    ->leftJoin('languages AS l_color', 'l_color.id', '=', 'color_trans.language_id')
                    ->where('l_color.code', $lang);
            })

            ->leftJoin('driver_type_translations AS driver_trans', function ($join) use ($lang) {
                $join->on('driver_trans.driver_type_id', '=', 'listings.driver_type_id')
                    ->leftJoin('languages AS l_driver', 'l_driver.id', '=', 'driver_trans.language_id')
                    ->where('l_driver.code', $lang);
            })

            ->leftJoin('category_translations AS cat_trans', function ($join) use ($lang) {
                $join->on('cat_trans.category_id', '=', 'listings.category_id')
                    ->leftJoin('languages AS l_cat', 'l_cat.id', '=', 'cat_trans.language_id')
                    ->where('l_cat.code', $lang);
            })

            ->leftJoin('location_translations AS loc_trans', function ($join) use ($lang) {
                $join->on('loc_trans.location_id', '=', 'listings.location_id')
                    ->leftJoin('languages AS l_loc', 'l_loc.id', '=', 'loc_trans.language_id')
                    ->where('l_loc.code', $lang);
            })

            // ------------------ CONDITIONS ------------------
            ->where('listings.user_id', $userId)
            ->where('listings.status', 'published')

            ->with(['photos', 'user'])
            ->orderByDesc('listings.created_at');

        return ListingResource::collection(
            $query->paginate($perPage)
        );
    }

}
