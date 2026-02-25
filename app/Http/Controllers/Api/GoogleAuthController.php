<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{

    /**
     * @var GoogleAuthService
     */
    protected $service;


    /**
     * @param GoogleAuthService $service
     */
    public function __construct(GoogleAuthService $service)
    {
        $this->service = $service;
    }


    /**
     * @return mixed
     */
    public function redirectToGoogle()
    {

        return Socialite::driver('google')->stateless()->redirect();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        $googleUser = $this->service->getGoogleUser();
        $user =  $this->service->findOrCreateUser($googleUser);
        $token =  $this->service->generateToken($user);

        return redirect()->away(
            config('app.frontend_url') . "/auth/impersonate?token={$token}"
        );
    }


}
