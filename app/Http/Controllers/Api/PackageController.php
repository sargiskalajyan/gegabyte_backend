<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{

    /**
     * @param Request $request
     * @param Package $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function buy(Request $request, $lang, $id)
    {
        $user = $request->user();

        $package = Package::query()
            ->where('id', '=', $id)
            ->where('is_active', '=',  true)
            ->where('price', '>', 0)
            ->first();

        if (! $package) {
            return response()->json([
                'message' => __('payments.package_found')
            ], 404);
        }

        $idempotency = $request->input(
            'idempotency_key',
            (string) Str::uuid()
        );

        $existing = Order::where('idempotency_key', $idempotency)->first();
        if ($existing) {
            return response()->json(['order' => $existing], 200);
        }

        $order = null;
        DB::transaction(function () use ($user, $package, $idempotency, &$order) {
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'AMD',
                'gateway' => 'evoca',
                'status' => 'pending',
                'idempotency_key' => $idempotency,
            ]);
        });

        /**
         * 🔥 AUTO WEBHOOK FOR TESTING
         */
        Http::post(
            url("/api/{$request->route('lang')}/payments/webhook/evoca"),
            [
                'order_id' => $order->id,
                'transaction_id' => 'TEST-' . Str::uuid(),
                'amount' => $order->amount,
                'status' => 'success',
            ]
        );

        $order->refresh();

        return response()->json([
            'message' => __('payments.order_created'),
            'order' => $order,
        ], 201);
    }



}
