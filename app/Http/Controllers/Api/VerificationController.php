<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyCodeMail;
use Illuminate\Http\Request;

class VerificationController extends Controller
{

    /**
     * @param Request $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function verifyCode(Request $request, $lang)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|digits:6',
        ]);

        $cachedCode = cache()->get("verify_code_{$request->email}");

        if (!$cachedCode) {
            return response()->json([
                'message' => __('auth.code_expired')
            ], 422);
        }

        if ($cachedCode != $request->code) {
            return response()->json([
                'message' => __('auth.invalid_code')
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->email_verified_at = now();
        $user->save();

        cache()->forget("verify_code_{$user->email}");

        return response()->json([
            'message' => __('auth.email_verified_success'),
        ]);
    }

    /**
     * Resend code (again no DB)
     */
    public function resend(Request $request, $lang)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => __('auth.user_not_found'),
            ], 404);
        }

        $code = rand(100000, 999999);

        cache()->put("verify_code_{$user->email}", $code, now()->addMinutes(10));

        $user->notify(new VerifyCodeMail($code));

        return response()->json([
            'message' => __('auth.verification_sent'),
        ]);
    }

}
