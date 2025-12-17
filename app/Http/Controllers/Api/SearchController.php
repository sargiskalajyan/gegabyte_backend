<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingSearchRequest;
use App\Http\Resources\ListingResource;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Color;
use App\Models\Condition;
use App\Models\Currency;
use App\Models\DriverType;
use App\Models\Drivetrain;
use App\Models\Engine;
use App\Models\EngineSize;
use App\Models\Fuel;
use App\Models\Language;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Make;
use App\Models\Package;
use App\Models\Transmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{


    /**
     * @param ListingSearchRequest $request
     * @param $lang
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ListingSearchRequest $request, $lang)
    {
        $validated = $request->validated();

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

            // ------------------ MAKES ------------------
            ->leftJoin('makes', 'makes.id', '=', 'listings.make_id')
            ->leftJoin('make_translations AS make_trans', function ($join) use ($lang) {
                $join->on('make_trans.make_id', '=', 'makes.id')
                    ->leftJoin('languages AS l_make', 'l_make.id', '=', 'make_trans.language_id')
                    ->where('l_make.code', $lang);
            })

            // ------------------ MODELS ------------------
            ->leftJoin('car_models', 'car_models.id', '=', 'listings.car_model_id')
            ->leftJoin('car_model_translations AS model_trans', function ($join) use ($lang) {
                $join->on('model_trans.car_model_id', '=', 'car_models.id')
                    ->leftJoin('languages AS l_model', 'l_model.id', '=', 'model_trans.language_id')
                    ->where('l_model.code', $lang);
            })

            // ------------------ FUEL ------------------
            ->leftJoin('fuels', 'fuels.id', '=', 'listings.fuel_id')
            ->leftJoin('fuel_translations AS fuel_trans', function ($join) use ($lang) {
                $join->on('fuel_trans.fuel_id', '=', 'fuels.id')
                    ->leftJoin('languages AS l_fuel', 'l_fuel.id', '=', 'fuel_trans.language_id')
                    ->where('l_fuel.code', $lang);
            })

            // ------------------ TRANSMISSION ------------------
            ->leftJoin('transmissions', 'transmissions.id', '=', 'listings.transmission_id')
            ->leftJoin('transmission_translations AS trans_trans', function ($join) use ($lang) {
                $join->on('trans_trans.transmission_id', '=', 'transmissions.id')
                    ->leftJoin('languages AS l_trans', 'l_trans.id', '=', 'trans_trans.language_id')
                    ->where('l_trans.code', $lang);
            })

            // ------------------ DRIVETRAIN ------------------
            ->leftJoin('drivetrains', 'drivetrains.id', '=', 'listings.drivetrain_id')
            ->leftJoin('drivetrain_translations AS drive_trans', function ($join) use ($lang) {
                $join->on('drive_trans.drivetrain_id', '=', 'drivetrains.id')
                    ->leftJoin('languages AS l_drive', 'l_drive.id', '=', 'drive_trans.language_id')
                    ->where('l_drive.code', $lang);
            })

            // ------------------ CONDITION ------------------
            ->leftJoin('conditions', 'conditions.id', '=', 'listings.condition_id')
            ->leftJoin('condition_translations AS cond_trans', function ($join) use ($lang) {
                $join->on('cond_trans.condition_id', '=', 'conditions.id')
                    ->leftJoin('languages AS l_cond', 'l_cond.id', '=', 'cond_trans.language_id')
                    ->where('l_cond.code', $lang);
            })

            // ------------------ COLOR ------------------
            ->leftJoin('colors', 'colors.id', '=', 'listings.color_id')
            ->leftJoin('color_translations AS color_trans', function ($join) use ($lang) {
                $join->on('color_trans.color_id', '=', 'colors.id')
                    ->leftJoin('languages AS l_color', 'l_color.id', '=', 'color_trans.language_id')
                    ->where('l_color.code', $lang);
            })

            // ------------------ DRIVER TYPE ------------------
            ->leftJoin('driver_types', 'driver_types.id', '=', 'listings.driver_type_id')
            ->leftJoin('driver_type_translations AS driver_trans', function ($join) use ($lang) {
                $join->on('driver_trans.driver_type_id', '=', 'driver_types.id')
                    ->leftJoin('languages AS l_driver', 'l_driver.id', '=', 'driver_trans.language_id')
                    ->where('l_driver.code', $lang);
            })

            // ------------------ CATEGORY ------------------
            ->leftJoin('categories', 'categories.id', '=', 'listings.category_id')
            ->leftJoin('category_translations AS cat_trans', function ($join) use ($lang) {
                $join->on('cat_trans.category_id', '=', 'categories.id')
                    ->leftJoin('languages AS l_cat', 'l_cat.id', '=', 'cat_trans.language_id')
                    ->where('l_cat.code', $lang);
            })

            // ------------------ LOCATION ------------------
            ->leftJoin('locations', 'locations.id', '=', 'listings.location_id')
            ->leftJoin('location_translations AS loc_trans', function ($join) use ($lang) {
                $join->on('loc_trans.location_id', '=', 'locations.id')
                    ->leftJoin('languages AS l_loc', 'l_loc.id', '=', 'loc_trans.language_id')
                    ->where('l_loc.code', $lang);
            })

            ->with(['photos', 'user']); // eager loading


        // ------------------ APPLY FILTERS ------------------
        $filters = [
            'make_id'         => 'listings.make_id',
            'model_id'        => 'listings.car_model_id',
            'fuel_id'         => 'listings.fuel_id',
            'transmission_id' => 'listings.transmission_id',
            'location_id'     => 'listings.location_id',
        ];

        foreach ($filters as $input => $column) {
            if (!empty($validated[$input])) {
                $query->where($column, $validated[$input]);
            }
        }

        if (!empty($validated['price_from'])) {
            $query->where('listings.price', '>=', $validated['price_from']);
        }

        if (!empty($validated['price_to'])) {
            $query->where('listings.price', '<=', $validated['price_to']);
        }

        if (!empty($validated['year_from'])) {
            $query->where('listings.year', '>=', $validated['year_from']);
        }

        if (!empty($validated['year_to'])) {
            $query->where('listings.year', '<=', $validated['year_to']);
        }

        if (!empty($validated['keyword'])) {
            $query->where('listings.description', 'LIKE', "%{$validated['keyword']}%");
        }

        // Order & paginate
        $query->orderBy('listings.created_at', 'DESC');

        $listings = $query->paginate($validated['per_page'] ?? 20);

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
        $listing = Listing::with('photos')
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            return response()->json(['message' => __('listings.not_found')], 404);
        }

        $listing->loadTranslationAttributes();
        return new ListingResource($listing);
    }



    /**
     * @param $lang
     * @return mixed
     */
    public function list($lang)
    {
        return Cache::remember("filters_$lang", 3600, function () {
            return [
                'categories'    => Category::with('translation')->get(),
                'fuels'         => Fuel::with('translation')->get(),
                'transmissions' => Transmission::with('translation')->get(),
                'drivetrains'   => Drivetrain::with('translation')->get(),
                'conditions'    => Condition::with('translation')->get(),
                'locations'     => Location::with('translation')->get(),
                'makes'         => Make::with('translation')->get(),
                'models'        => CarModel::with('translation')->get(),
                'colors'        => Color::with('translation')->get(),
                'driver_types'  => DriverType::with('translation')->get(),
                'currencies'    => Currency::with('translation')->get(),
                'engine_sizes'  => EngineSize::with('translation')->get(),
                'engines'       => Engine::with('translation')->get(),
                'packages'      => Package::with('translation')->get(),
                'languages'     => Language::all(),
                'years'         => range(date('Y'), 1980),
            ];
        });
    }


    /**
     * @param Request $request
     * @param $lang
     * @param $makeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function models($lang, $makeId)
    {
        $models = CarModel::query()
            ->where('make_id', $makeId)
            ->leftJoin('car_model_translations AS model_trans', function ($join) use ($lang) {
                $join->on('model_trans.car_model_id', '=', 'car_models.id')
                    ->leftJoin('languages AS l_model', 'l_model.id', '=', 'model_trans.language_id')
                    ->where('l_model.code', $lang);
            })
            ->select([
                'car_models.id',
                'car_models.make_id',
                'model_trans.name'
            ])
            ->orderBy('model_trans.name', 'ASC')
            ->get();

        return response()->json($models);
    }

}
