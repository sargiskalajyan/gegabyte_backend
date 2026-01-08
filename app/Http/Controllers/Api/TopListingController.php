<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Services\TopListingService;

class TopListingController extends Controller
{
    public function store(Request $request, Listing $listing, TopListingService $service)
    {
        $this->authorize('update', $listing);

        $request->validate([
            'days' => 'nullable|integer|min:1',
        ]);

        try {
            $service->assignTop($listing, $request->user(), $request->input('days'));
            return response()->json(['success' => true, 'listing' => $listing]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, Listing $listing, TopListingService $service)
    {
        $this->authorize('update', $listing);

        try {
            $service->revokeTop($listing, $request->user());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
