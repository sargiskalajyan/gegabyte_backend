<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Package;
use App\Models\Category;

class FiltersController extends Controller
{


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(Request $request)
    {
        $code = (string) $request->route('lang') ?? (string) app()->getLocale();
        $cacheKey = "filters_{$code}_categories";

        $data = Cache::remember($cacheKey, 3600, function () {
            return Category::with('translation')->get();
        });

        return response()->json($data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function packages(Request $request)
    {
        $code = (string) $request->route('lang') ?? (string) app()->getLocale();

        $cacheKey = "filters_{$code}_packages";

        $data = Cache::remember($cacheKey, 3600, function () {
            return Package::with('translation')->get();
        });

        return response()->json($data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function advertisements(Request $request)
    {
        $code = (string) $request->route('lang') ?? (string) app()->getLocale();

        $cacheKey = "filters_{$code}_advertisements";

        $data = Cache::remember($cacheKey, 3600, function () {
            return Advertisement::with('translation')->get();
        });

        return response()->json($data);
    }
}
