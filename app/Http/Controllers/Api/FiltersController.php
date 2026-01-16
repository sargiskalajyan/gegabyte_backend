<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Package;
use App\Models\Category;

class FiltersController extends Controller
{
    public function categories(Request $request)
    {
        $code = (string) $request->route('lang') ?? (string) app()->getLocale();
        $cacheKey = "filters_{$code}_categories";

        $data = Cache::remember($cacheKey, 3600, function () {
            return Category::with('translation')->get();
        });

        return response()->json($data);
    }

    public function packages(Request $request)
    {
        $code = (string) $request->route('lang') ?? (string) app()->getLocale();

        $cacheKey = "filters_{$code}_packages";

        $data = Cache::remember($cacheKey, 3600, function () {
            return Package::with('translation')->get();
        });

        return response()->json($data);
    }
}
