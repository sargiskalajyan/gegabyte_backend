<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\UserPackageResource;

class UserPackageController extends Controller
{

    /**
     * @return JsonResponse
     */
    public function getPackageStats(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $activeCount = DB::table('listings')
            ->where('user_id', $user->id)
            ->count();

        $totalTopUsed = DB::table('listings')
            ->where('user_id', $user->id)
            ->where('is_top', true)
            ->where('top_expires_at', '>', now())
            ->count();

        $activePackageRecord = $user->activePackage();
        $active = $activePackageRecord;

        // Determine posts used for active package from actual listings if counters are not reliable
        $activePostsLimit = $active->package?->max_active_listings ?? null;
        $activePostsUsedFromCounter = $active->used_active_listings ?? null;
        $activePostsUsed = is_null($activePostsUsedFromCounter) || $activePostsUsedFromCounter === 0
            ? min($activeCount, $activePostsLimit ?? PHP_INT_MAX)
            : $activePostsUsedFromCounter;

        $activeTopSlots = $active->package?->top_listings_count ?? 0;
        $activeTopUsedFromCounter = $active->used_top_listings ?? null;
        $activeTopUsed = is_null($activeTopUsedFromCounter) || $activeTopUsedFromCounter === 0
            ? $totalTopUsed
            : $activeTopUsedFromCounter;

        $activePackageOutput = [
            'package_id' => $active->package_id ?? null,
            'package_name' => $active->package?->name ?? null,
            'expiration_date' => $active->expires_at ?? null,
            'posts_limit' => $activePostsLimit ?? null,
            'posts_used' => $activePostsUsed,
            'posts_remaining' => is_null($activePostsLimit) ? null : max(0, ($activePostsLimit ?? 0) - $activePostsUsed),
            'top_slots' => $activeTopSlots,
            'top_used' => $activeTopUsed,
            'top_remaining' => max(0, ($activeTopSlots ?? 0) - $activeTopUsed),
        ];

        return response()->json([
            'success' => true,
            'data' => $activePackageOutput
        ]);
    }
}
