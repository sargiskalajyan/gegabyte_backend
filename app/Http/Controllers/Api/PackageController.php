<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{



    /**
     * @param Request $request
     * @param Package $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function buy(Request $request, Package $package)
    {
        $user = $request->user();
        $idempotency = $request->input('idempotency_key', Str::uuid()->toString());

        // Prevent duplicate orders by idempotency_key
        $existing = Order::where('idempotency_key', $idempotency)->first();
        if ($existing) {
            return response()->json(['order' => $existing], 200);
        }

        // Create order in a transaction
        $order = null;
        DB::transaction(function () use ($user, $package, $idempotency, &$order) {
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'AMD', // adapt as needed
                'gateway' => 'evoca', // default, client can choose
                'status' => 'pending',
                'idempotency_key' => $idempotency,
            ]);
        });

        // Example stub:
        $paymentUrl = url("/payments/redirect/{$order->id}");

        return response()->json([
            'message' => 'Order created',
            'order' => $order,
            'payment_url' => $paymentUrl
        ], 201);
    }
}
