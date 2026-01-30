<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AdvertisementController extends Controller
{
    /**
     * Buy advertisement option and create an order (idempotent)
     * Request must include `listing_id`.
     */
    public function buy(Request $request, $lang, $id)
    {
        app()->setLocale($lang);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        $ad = Advertisement::where('id', $id)->where('is_active', true)->first();
        if (! $ad) {
            return response()->json(['message' => __('payments.package_found')], 404);
        }

        $request->validate([
            'listing_id' => 'required|integer|exists:listings,id',
        ]);

        $listing = Listing::find($request->listing_id);
        if ($listing->user_id !== $user->id) {
            return response()->json(['message' => __('listings.forbidden')], 403);
        }

        $idempotency = $request->input('idempotency_key', (string) Str::uuid());
        $existing = Order::where('idempotency_key', $idempotency)->first();
        if ($existing) {
            return response()->json(['order' => $existing], 200);
        }

        $order = null;
        DB::transaction(function () use ($user, $ad, $listing, $idempotency, &$order) {
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => null,
                'advertisement_id' => $ad->id,
                'amount' => (int) $ad->price,
                'currency' => 'AMD',
                'gateway' => 'evoca',
                'status' => 'pending',
                'idempotency_key' => $idempotency,
                'payload' => [
                    'advertisement_id' => $ad->id,
                    'listing_id' => $listing->id,
                ],
            ]);
        });

        // Auto webhook for dev/testing like packages
        Http::post(url("/api/{$request->route('lang')}/payments/webhook/evoca"), [
            'order_id' => $order->id,
            'transaction_id' => 'TEST-' . Str::uuid(),
            'amount' => $order->amount,
            'status' => 'success',
        ]);

        $order->refresh();

        return response()->json([
            'message' => __('payments.order_created'),
            'order' => $order,
        ], 201);
    }
}
