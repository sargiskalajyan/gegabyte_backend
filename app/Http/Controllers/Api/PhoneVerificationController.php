<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class PhoneVerificationController extends Controller
{
    /**
     * Request SMS code for phone add/update
     */
    public function requestCode(Request $request, $lang, SmsService $smsService)
    {
        $langModel = Language::where('code', $lang)->first();
        if ($langModel) {
            app()->setLocale($langModel->code);
        }

        $user = auth('api')->user();

        $request->validate([
            'phone_number' => [
                'required',
                'string',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],
        ]);

        $phone = $request->phone_number;
        $code = rand(100000, 999999);

        Cache::put("pending_phone_{$user->id}", $phone, now()->addMinutes(5));
        Cache::put("phone_verify_{$user->id}", $code, now()->addMinutes(5));

        if ($user->phone_number && $user->phone_number_verified_at) {
            $user->phone_number_verified_at = null;
            $user->save();
        }

        $smsService->sendSms($phone, __('auth.phone_sms_code', ['code' => $code]));

        return response()->json([
            'message' => __('auth.phone_code_sent'),
        ]);
    }

    /**
     * Verify SMS code and save phone number
     */
    public function verifyCode(Request $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        if ($langModel) {
            app()->setLocale($langModel->code);
        }

        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = auth('api')->user();

        $pendingPhone = Cache::get("pending_phone_{$user->id}");
        $cachedCode = Cache::get("phone_verify_{$user->id}");

        if (!$pendingPhone || !$cachedCode) {
            return response()->json([
                'message' => __('auth.code_expired'),
            ], 422);
        }

        if ($cachedCode != $request->code) {
            return response()->json([
                'message' => __('auth.invalid_code'),
            ], 422);
        }

        $user->phone_number = $pendingPhone;
        $user->phone_number_verified_at = now();
        $user->save();

        Cache::forget("pending_phone_{$user->id}");
        Cache::forget("phone_verify_{$user->id}");

        return response()->json([
            'message' => __('auth.phone_verified_success'),
            'user' => new UserResource($user),
        ]);
    }

}
