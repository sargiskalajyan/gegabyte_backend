<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Order;
use App\Services\Payments\AmeriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
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
        $user = $request->user();
        
        // Prevent buying a paid package while user already has an active paid package
        $active = $user->activePackage();
        if ($active && ($active->package->price ?? 0) > 0) {
            return response()->json([
                'message' => __('payments.active_package_exists')
            ], 400);
        }

        $package = Package::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->first();

        if (!$package) {
            return response()->json([
                'message' => __('payments.package_found')
            ], 404);
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

        // Create pending order
        $order = null;

        DB::transaction(function () use ($user, $package, $idempotency, &$order) {

            $order = Order::create([
                'user_id'         => $user->id,
                'package_id'      => $package->id,
                'amount'          => $package->price,
                'currency'        => '051',
                'gateway'         => 'ameria',
                'status'          => 'pending',
                'idempotency_key' => $idempotency,
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
            'description' => "Package #{$order->id}",
            'back_url'    => route('ameria.callback', ['lang' => $lang]),
        ]);

        // Check if Ameria accepted request
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

        // Save PaymentID
        $order->update([
            'reference' => $response['PaymentID'],
            'payload'   => $response,
        ]);

        return response()->json([
            'message' => __('payments.order_created'),
            'redirect_url' => $ameria->paymentRedirectUrl($response['PaymentID'])
        ], 201);
    }
}
