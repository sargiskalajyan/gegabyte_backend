<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
//        $user = auth('api')->user();
//
//        if (!$user || !$user->phone_number_verified_at) {
//            return response()->json([
//                'message' => __('auth.phone_not_verified'),
//            ], 403);
//        }

        return $next($request);
    }
}
