<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect('/');
            }
        });
    }

    /**
     * Determine language from URL segment or Accept-Language header
     */
    protected function getRequestLanguage(Request $request): string
    {
        $lang = $request->segment(2) ?: $request->header('Accept-Language');

        $langModel = \App\Models\Language::where('code', $lang)->first();

        return $langModel->code ?? 'en';
    }

    /**
     * Handle unauthenticated (JWT) requests
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        app()->setLocale($this->getRequestLanguage($request));

        try {
            $token = JWTAuth::getToken();

            // No token provided
            if (!$token) {
                return response()->json([
                    'error' => __('auth.token_not_provided'),
                ], 401);
            }

            // Check token validity (without re-authenticating)
            JWTAuth::setToken($token)->check();

            return response()->json([
                'error' => __('auth.unauthenticated'),
            ], 401);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => __('auth.token_expired'),
            ], 419);

        } catch (JWTException $e) {
            return response()->json([
                'error' => __('auth.unauthenticated'),
            ], 401);
        }
    }

    /**
     * Render exceptions
     */
    public function render($request, Throwable $exception)
    {
        app()->setLocale($this->getRequestLanguage($request));

        return parent::render($request, $exception);
    }
}
