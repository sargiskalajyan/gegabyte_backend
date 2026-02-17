<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Listing;
use App\Models\Order;
use App\Models\UserPackage;
use App\Models\Package;
use App\Services\Payments\AmeriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    /**
     * @param Request $request
     * @param $gateway
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function webhook(Request $request, $gateway)
    {
        if (! $this->verifyGatewaySignature($request, $gateway)) {
            Log::warning('Invalid payment webhook signature', ['gateway' => $gateway]);
            return response(__('payments.invalid_signature'), 400);
        }

        $payload = $request->all();

        $reference = $payload['transaction_id'] ?? $payload['reference'] ?? null;
        $orderId   = $payload['order_id'] ?? null;

        $order = null;
        if ($reference) {
            $order = Order::where('reference', $reference)->first();
        }
        if (! $order && $orderId) {
            $order = Order::find($orderId);
        }

        if (! $order) {
            Log::warning('Webhook: order not found', ['payload' => $payload]);
            return response(__('payments.order_not_found'), 404);
        }

        if ($order->status === 'paid') {
            return response(__('payments.already_processed'), 200);
        }

        $status = $payload['status'] ?? 'unknown';

        if (in_array($status, ['success', 'paid', 'completed'])) {
            DB::transaction(function () use ($order, $payload, $reference) {
                $order->markPaid($reference, $payload);

                $user = $order->user;
                $package = $order->package;

                if ($package) {
                    $user->packages()->where('status', 'active')->update(['status' => 'expired']);

                    UserPackage::create([
                        'user_id' => $user->id,
                        'package_id' => $package->id,
                        'starts_at' => now(),
                        'expires_at' => $package->duration_days
                            ? now()->addDays($package->duration_days)
                            : null,
                        'used_active_listings' => 0,
                        'used_featured_days' => 0,
                        'status' => 'active'
                    ]);
                } else {
                    // Handle advertisement orders (payload expected to contain advertisement_id and listing_id)
                        // Try to detect advertisement from order record first, then from webhook payload
                        $payload = $payload ?? [];
                        $adId = $order->advertisement_id ?? ($payload['advertisement_id'] ?? null);
                        $listingId = $payload['listing_id'] ?? ($order->payload['listing_id'] ?? null);

                        if ($adId && $listingId) {
                            $ad = Advertisement::find($adId);
                            $listing = Listing::find($listingId);

                            if ($ad && $listing) {
                                $starts = now();
                                $expires = $ad->duration_days ? $starts->copy()->addDays($ad->duration_days) : null;

                                // ensure order.advertisement_id is set
                                if (! $order->advertisement_id) {
                                    $order->advertisement_id = $ad->id;
                                    $order->save();
                                }

                                // mark listing as top
                                $listing->is_top = true;
                                $listing->top_expires_at = $expires;
                                $listing->save();
                            }
                        } else {
                            Log::error('Paid order has no package and is not advertisement', ['order_id' => $order->id, 'payload' => $payload]);
                        }
                }
            });

            return response(__('payments.payment_success'), 200);
        }

        $order->update([
            'status' => 'failed',
            'payload' => array_merge($order->payload ?? [], $payload)
        ]);

        return response(__('payments.payment_failed'), 200);
    }


    /**
     * @param Request $request
     * @param $lang
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function ameriaCallback(Request $request, $lang)
    {
        $orderId   = $request->orderID;
        $paymentId = $request->paymentID;

        $frontend = rtrim(config('app.frontend_url'), '/');
        $isTestMode = (bool) config('services.ameria.test_mode');

        $order = Order::find($orderId);

        if (! $order && $isTestMode && $paymentId) {
            $order = Order::where('reference', $paymentId)->first();
        }

        if (! $order) {
            Log::warning('Ameria callback order not found', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'test_mode' => $isTestMode,
            ]);

            return redirect("{$frontend}/{$lang}/payment-failed");
        }

        $ameria = new AmeriaService();
        $details = $ameria->getPaymentDetails($paymentId);

        $opaqueData = [];
        if (! empty($details['Opaque'])) {
            $decodedOpaque = json_decode($details['Opaque'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $opaqueData = $decodedOpaque;
            } else {
                Log::warning('Ameria callback opaque parsing failed', [
                    'payment_id' => $paymentId,
                    'opaque' => $details['Opaque'],
                ]);
            }
        }

        if (($details['ResponseCode'] ?? null) === "00"
            && ($details['OrderStatus'] ?? null) == 2) {

            DB::transaction(function () use ($order, $details, $opaqueData) {

                $payload = $order->payload ?? [];

                if (! isset($payload['listing_id']) && isset($opaqueData['listing_id'])) {
                    $payload['listing_id'] = $opaqueData['listing_id'];
                }

                if (! isset($payload['advertisement_id']) && isset($opaqueData['advertisement_id'])) {
                    $payload['advertisement_id'] = $opaqueData['advertisement_id'];
                }

                $payload['ameria_details'] = $details;

                if (! $order->advertisement_id && isset($payload['advertisement_id'])) {
                    $order->advertisement_id = $payload['advertisement_id'];
                }

                $order->status = 'paid';
                $order->payload = $payload;
                $order->save();

                $order->load('package', 'advertisement', 'user');

                // Package activation
                if ($order->package) {
                    $user = $order->user;
                    $package = $order->package;

                    $user->packages()->where('status','active')
                        ->update(['status'=>'expired']);

                    UserPackage::create([
                        'user_id'    => $user->id,
                        'package_id' => $package->id,
                        'starts_at'  => now(),
                        'expires_at' => $package->duration_days
                            ? now()->addDays($package->duration_days)
                            : null,
                        'status'     => 'active'
                    ]);
                }

                // Advertisement activation
                if ($order->advertisement) {

                    $listingId = $payload['listing_id'] ?? ($opaqueData['listing_id'] ?? null);

                    if (! $listingId) {
                        Log::warning('Advertisement order missing listing reference', [
                            'order_id' => $order->id,
                        ]);
                        return;
                    }

                    $listing = Listing::find($listingId);

                    if (! $listing) {
                        Log::warning('Listing not found for advertisement order', [
                            'order_id' => $order->id,
                            'listing_id' => $listingId,
                        ]);
                        return;
                    }

                    $expires = $order->advertisement->duration_days
                        ? now()->addDays($order->advertisement->duration_days)
                        : null;

                    $listing->update([
                        'is_top'        => true,
                        'top_expires_at'=> $expires
                    ]);
                }
            });
            return redirect("{$frontend}/{$lang}/payment-success");
        }

        $order->update([
            'status' => 'failed',
            'payload'=> $details
        ]);

        return redirect("{$frontend}/{$lang}/payment-failed");
    }




    /**
     * @param Request $request
     * @param string $gateway
     * @return bool
     */
    protected function verifyGatewaySignature(Request $request, string $gateway): bool
    {
        // Implement HMAC or provider verification here.
        // For now return true for dev; change before production.
        return true;


    }


    /**
     * @param Request $request
     * @param $lang
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request, $lang, $id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'message' => __('payments.order_not_found')
            ], 404);
        }

        return response()->json([
            'message' => __('payments.order_status'),
            'order'   => $order
        ]);
    }


    /**
     * Get authenticated user's payment history (paginated) with translations
     *
     * @param Request $request
     * @param string $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request, $lang)
    {
        $user = $request->user();

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $orders = Order::where('user_id', $user->id)
            ->with(['package.translations', 'package.translation', 'advertisement.translations', 'advertisement.translation'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $orders->getCollection()->transform(function (Order $order) {
            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'amount' => $order->amount,
                'status' => $order->status,
                'gateway' => $order->gateway,
                'payload' => $order->payload,
                'description' => $order->description,
                'created_at' => $order->created_at,
                'package' => $order->package ? [
                        'id' => $order->package->id,
                        'price' => $order->package->price,
                        'duration_days' => $order->package->duration_days,
                        'name' => $order->package->name,
                    ] : null,
                'advertisement' => $order->advertisement ? [
                        'id' => $order->advertisement->id,
                        'price' => $order->advertisement->price,
                        'duration_days' => $order->advertisement->duration_days,
                        'name' => $order->advertisement->name,
                    ] : null,
            ];
        });

        return response()->json([
            'message' => __('payments.history'),
            'data' => $orders
        ]);
    }
}
