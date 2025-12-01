<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
    protected function getRequestLanguage($request)
    {
        $lang = $request->segment(2) ?: $request->header('Accept-Language');
        $langModel = \App\Models\Language::where('code', $lang)->first();
        return $langModel->code ?? 'en';
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Always set locale for translations
        app()->setLocale($this->getRequestLanguage($request));

        // Handle JWT / Authentication exceptions
        if ($exception instanceof AuthenticationException) {
            try {
                $token = JWTAuth::getToken();
                JWTAuth::checkOrFail($token);
            } catch (TokenExpiredException $e) {
                // Ensure locale is set inside catch
                app()->setLocale($this->getRequestLanguage($request));

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => __('auth.token_expired'),
                        'validation' => false
                    ], 419);
                }

                return redirect()->guest('/')
                    ->withErrors(['token' => __('auth.session_expired')]);
            } catch (JWTException $e) {
                // Ensure locale is set inside catch
                app()->setLocale($this->getRequestLanguage($request));

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => __('auth.unauthenticated'),
                    ], 403);
                }

                return redirect()->guest('/')
                    ->withErrors(['auth' => __('auth.unauthenticated')]);
            }
        }

        return parent::render($request, $exception);
    }
}
