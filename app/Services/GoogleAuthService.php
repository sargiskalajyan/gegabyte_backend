<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleAuthService
{


    /**
     * @return mixed
     */
    public function getGoogleUser()
    {
        return Socialite::driver('google')->stateless()->user();
    }


    /**
     * @param $providerUser
     * @return mixed
     */
    public function findOrCreateUser($providerUser)
    {
        $user = User::where('email', $providerUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'username' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'password' => Hash::make(str()->random(16)),
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }


    /**
     * @param $user
     * @return mixed
     */
    public function generateToken($user)
    {
        return JWTAuth::fromUser($user);
    }
}
