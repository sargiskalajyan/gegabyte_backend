<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ImpersonateController extends Controller
{

    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(User $user)
    {
        $token = JWTAuth::fromUser($user);

        return redirect()->away(
            config('app.frontend_url') . "/auth/impersonate?token={$token}"
        );
    }
}
