<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Listing;
use App\Models\Order;
use App\Services\Payments\AmeriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AdvertisementController extends Controller
{


    /**
     * @param Request $request
     * @param $lang
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Random\RandomException
     */
    public function buy(Request $request, $lang, $id)
    {
        app()->setLocale($lang);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => __('auth.unauthenticated')
            ], 401);
        }

        $ad = Advertisement::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$ad) {
            return response()->json([
                'message' => __('payments.package_found')
            ], 404);
        }

        $request->validate([
            'listing_id' => 'required|integer|exists:listings,id',
        ]);

        $listing = Listing::findOrFail($request->listing_id);

        if ($listing->user_id !== $user->id) {
            return response()->json([
                'message' => __('listings.forbidden')
            ], 403);
        }

        // Prevent buying advertisement for a listing that's already top
        if ($listing->is_top && (is_null($listing->top_expires_at) || $listing->top_expires_at->isFuture())) {
            return response()->json([
                'message' => __('listings.already_top')
            ], 400);
        }

        // Idempotency
        $idempotency = $request->input(
            'idempotency_key',
            (string) Str::uuid()
        );

        $existing = Order::where('idempotency_key', $idempotency)->first();
        if ($existing) {
            return response()->json([
                'order' => $existing
            ], 200);
        }

        // Create order (pending)
        $order = null;

        DB::transaction(function () use ($user, $ad, $listing, $idempotency, &$order) {

            $order = Order::create([
                'user_id'          => $user->id,
                'package_id'       => null,
                'advertisement_id' => $ad->id,
                'amount'           => (int) $ad->price,
                'currency'         => '051', // ISO for AMD (Ameria)
                'gateway'          => 'ameria',
                'status'           => 'pending',
                'idempotency_key'  => $idempotency,
                'payload'          => [
                    'advertisement_id' => $ad->id,
                    'listing_id'       => $listing->id,
                ],
            ]);
        });

        // Call Ameria InitPayment
        $ameria = new AmeriaService();

        $externalOrderId = config('services.ameria.test_mode')
            ? random_int(4184001, 4185000)
            : $order->id;

        $amount = config('services.ameria.test_mode')
            ? 10
            : $order->amount;

        $response = $ameria->initPayment([
            'amount'      => $amount,
            'order_id'    => $externalOrderId,
            'description' => "Advertisement purchase #{$order->id}",
            'back_url'    => route('ameria.callback', ['lang' => $lang]),
            'opaque'      => json_encode([
                'advertisement_id' => $ad->id,
                'listing_id'       => $listing->id,
            ])
        ]);

        // If Ameria failed to initialize
        if (($response['ResponseCode'] ?? null) != 1) {

            $order->update([
                'status'  => 'failed',
                'payload' => $response
            ]);

            return response()->json([
                'message' => 'Payment initialization failed',
                'error'   => $response
            ], 400);
        }

        // Save PaymentID returned by Ameria
        $order->update([
            'reference' => $response['PaymentID'],
            'payload'   => array_merge($order->payload ?? [], $response),
        ]);

        return response()->json([
            'message'      => __('payments.order_created'),
            'redirect_url' => $ameria->paymentRedirectUrl($response['PaymentID'])
        ], 201);
    }
}
