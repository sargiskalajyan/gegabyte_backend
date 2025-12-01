<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $lang = $request->route('lang');

        // allowed languages should exist in database
        $allowed = Language::pluck('code')->toArray();

        if (!in_array($lang, $allowed)) {
            return response()->json(['message' => 'Invalid language'], 400);
        }

        app()->setLocale($lang);
        return $next($request);
    }
}
