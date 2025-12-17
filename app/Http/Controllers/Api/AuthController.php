<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\User;
use App\Notifications\VerifyCodeMail;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    /**
     * @param $seconds
     * @return string
     */
    private function formatExpiry($seconds)
    {
        $dt1 = now();
        $dt2 = now()->addSeconds($seconds);

        return $dt1->diff($dt2)->format('%y years %m months %d days %h hours %i minutes %s seconds');
    }


    /**
     * @param RegisterRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        app()->setLocale($langModel->code);

        $data = $request->validated();

        $user = User::create([
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'language_id'  => $langModel->id,
        ]);

        // ⭐ Automatically assign FREE package on registration
        $user->activePackage();

        // Generate 6-digit verification code
        $code = rand(100000, 999999);

        cache()->put("verify_code_{$user->email}", $code, now()->addMinutes(10));

        // Send verification email
        $user->notify(new VerifyCodeMail($code));

        return response()->json([
            'message' => __('auth.registered_check_email'),
        ], 201);
    }


    /**
     * @param LoginRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, $lang)
    {
        app()->setLocale($lang);

        $credentials = $request->validated();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        $user       = auth('api')->user();
        $seconds    = auth('api')->factory()->getTTL() * 60;

        return response()->json([
            'access_token' => $token,
            'expires_in'   => [
                'seconds' => $seconds,
                'readable'=> $this->formatExpiry($seconds),
            ],
            'user'         => new UserResource($user),
        ]);
    }


    /**
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout($lang)
    {
        app()->setLocale($lang);

        auth('api')->logout();

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }


    /**
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh($lang)
    {
        app()->setLocale($lang);
        $token   = auth('api')->refresh();
        $seconds = auth('api')->factory()->getTTL() * 60;

        return response()->json([
            'access_token' => $token,
            'expires_in'   => [
                'seconds' => $seconds,
                'readable'=> $this->formatExpiry($seconds),
            ],
        ]);
    }


    /***
     * @param Request $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        app()->setLocale($langModel->code);

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

        cache()->put("reset_password_code_{$user->email}", $code, now()->addMinutes(10));

        $user->notify(new VerifyCodeMail($code));

        return response()->json([
            'message' => __('auth.reset_code_sent'),
        ]);
    }


    /**
     * @param ResetPasswordRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function resetPassword(ResetPasswordRequest $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        app()->setLocale($langModel->code);

        $cachedCode = cache()->get("reset_password_code_{$request->email}");
        if (!$cachedCode) {
            return response()->json([
                'message' => __('auth.code_expired'),
            ], 422);
        }

        if ($cachedCode != $request->code) {
            return response()->json([
                'message' => __('auth.invalid_code'),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        cache()->forget("reset_password_code_{$user->email}");

        return response()->json([
            'message' => __('auth.password_reset_success'),
        ]);
    }


    /**
     * @param ChangePasswordRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        app()->setLocale($langModel->code);

        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);

        $user = auth('api')->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => __('auth.incorrect_old_password'),
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => __('auth.password_changed_success'),
        ]);
    }


    /**
     * @param UpdateProfileRequest $request
     * @param $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request, $lang)
    {
        $langModel = Language::where('code', $lang)->first();
        app()->setLocale($langModel->code);

        $user = auth('api')->user();

        $request->validate([
            'username'      => ['nullable', 'string', 'max:255'],
            'phone_number'  => [
                'nullable',
                'string',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],
            'language_id'   => ['nullable', 'exists:languages,id'],
            'location_id'   => ['nullable', 'exists:locations,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('profile_image')
                ->store('profiles', 'public');

            $user->profile_image = $path;
        }

        // Update other fields
        $user->update([
            'username'     => $request->username ?? $user->username,
            'phone_number' => $request->phone_number ?? $user->phone_number,
            'language_id'  => $request->language_id ?? $user->language_id,
            'location_id'  => $request->location_id ?? $user->location_id,
        ]);

        return response()->json([
            'message' => __('auth.profile_updated'),
            'user'    => new UserResource($user),
        ]);
    }

}
