<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{


    /**
     * @param Request $request
     * @param $gateway
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function webhook(Request $request, $gateway)
    {
        // 1) Verify signature — implement per gateway
        if (! $this->verifyGatewaySignature($request, $gateway)) {
            Log::warning('Invalid payment webhook signature', ['gateway' => $gateway]);
            return response('Invalid signature', 400);
        }

        $payload = $request->all();

        // 2) Extract idempotency or reference
        $reference = $payload['transaction_id'] ?? $payload['reference'] ?? null;
        $orderId = $payload['order_id'] ?? null; // if gateway returns your order id
        $amount = $payload['amount'] ?? null;

        // 3) Find order (try by reference then by our order id or by metadata)
        $order = null;
        if ($reference) {
            $order = Order::where('reference', $reference)->first();
        }
        if (! $order && $orderId) {
            $order = Order::find($orderId);
        }
        // If still not found, try to match amount + user + pending (best-effort) — optional

        if (! $order) {
            // If gateway returns our idempotency key somewhere, match it; otherwise log and return 404
            Log::warning('Webhook: order not found', ['payload' => $payload]);
            return response('Order not found', 404);
        }

        // Idempotent handling: if already paid, ignore
        if ($order->status === 'paid') {
            return response('Already processed', 200);
        }

        // Check gateway status in payload (this field depends on provider)
        $status = $payload['status'] ?? 'unknown';

        if (in_array($status, ['success','paid','completed'])) {
            DB::transaction(function () use ($order, $payload, $reference) {
                // Mark order paid
                $order->markPaid($reference, $payload);

                // Create / activate user package
                $user = $order->user;
                $package = $order->package;
                if (! $package) {
                    // no package attached — log and stop
                    Log::error('Paid order has no package', ['order_id' => $order->id]);
                    return;
                }

                // Deactivate previous active package(s) if you want single active
                $user->packages()->where('status', 'active')->update(['status' => 'expired']);

                $userPackage = UserPackage::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'starts_at' => now(),
                    'expires_at' => $package->duration_days ? now()->addDays($package->duration_days) : null,
                    'used_active_listings' => 0,
                    'used_featured_days' => 0,
                    'status' => 'active'
                ]);

                // Notify user (locale-aware) — implement notifications class
                // $user->notify(new PackageActivated($userPackage));
            });

            return response('OK', 200);
        }

        // Payment failed
        $order->update([
            'status' => 'failed',
            'payload' => array_merge($order->payload ?? [], $payload)
        ]);

        // Notify user about failed payment if needed

        return response('Ignored', 200);
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
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Order $order)
    {
        return response()->json(['order' => $order]);
    }
}
