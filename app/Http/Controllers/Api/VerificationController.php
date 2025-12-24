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
            'email' => 'required|string|max:255|exists:users,email',
            'code'  => 'required|digits:6',
        ]);

        $email = $request->email;
        $cachedCode = cache()->get("verify_code_{$email}");
        $attemptsKey = "verify_code_attempts_{$email}";

        // Check if user is temporarily blocked
        $attemptsData = cache()->get($attemptsKey, ['count' => 0, 'blocked_until' => null]);

        if ($attemptsData['blocked_until'] && now()->lessThan($attemptsData['blocked_until'])) {
            $waitSeconds = now()->diffInSeconds($attemptsData['blocked_until']);
            return response()->json([
                'message' => __('auth.too_many_attempts', ['minutes' => ceil($waitSeconds / 60)])
            ], 429);
        }

        if (!$cachedCode) {
            return response()->json([
                'message' => __('auth.code_expired')
            ], 422);
        }

        if ($cachedCode != $request->code) {
            // Increment attempt count
            $attemptsData['count']++;

            if ($attemptsData['count'] >= 3) {
                // Block for 10 minutes
                $attemptsData['blocked_until'] = now()->addMinutes(10);
                $attemptsData['count'] = 0; // reset attempts after blocking
            }

            cache()->put($attemptsKey, $attemptsData, $attemptsData['blocked_until'] ?? now()->addMinutes(10));

            return response()->json([
                'message' => __('auth.invalid_code')
            ], 422);
        }

        // Success: reset attempts
        cache()->forget($attemptsKey);

        $user = User::where('email', $email)->first();
        $user->email_verified_at = now();
        $user->save();

        cache()->forget("verify_code_{$email}");

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
