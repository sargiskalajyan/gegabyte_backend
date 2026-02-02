<?php

use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\FiltersController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPackageController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});



Route::group([
    'prefix' => '{lang}',
    'middleware' => 'set.api.locale'
], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::group(['prefix' => 'email'], function () {
        Route::post('/verify', [VerificationController::class, 'verifyCode']);
        Route::post('/resend', [VerificationController::class, 'resend']);
    });

    Route::group(['prefix' => 'password'], function () {
        Route::post('/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('/verify-code', [AuthController::class, 'verifyResetCode']);
        Route::post('/reset',  [AuthController::class, 'resetPassword']);
    });

    Route::group(['prefix' => 'search'], function () {
        Route::get('/', [SearchController::class, 'index']);
        Route::get('/list',  [SearchController::class, 'list']);
        Route::get('/listings',  [SearchController::class, 'listings']);
        Route::get('/top-listings', [SearchController::class, 'topListings']);
        Route::get('/models',  [SearchController::class, 'models']);
        Route::get('/makes/{id}',  [SearchController::class, 'makes']);
        Route::get('/listing/{id}',  [SearchController::class, 'show']);
    });



    Route::group(['prefix' => 'filters'], function () {
        Route::get('/categories', [FiltersController::class, 'categories']);
        Route::get('/packages', [FiltersController::class, 'packages']);
        Route::get('/advertisements', [FiltersController::class, 'advertisements']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('/{user}/listings', [UserController::class, 'userListings']);
    });

    Route::post('payments/webhook/{gateway}', [OrderController::class, 'webhook']);

    Route::middleware('auth:api')->group(function () {
        Route::post('refresh',  [AuthController::class, 'refresh']);
        Route::post('logout',   [AuthController::class, 'logout']);
        Route::post('password/change', [AuthController::class, 'changePassword']);
        Route::post('profile/update',   [AuthController::class, 'updateProfile']);
        Route::get('user',  [AuthController::class, 'user']);
        Route::get('user/package-stats', [UserPackageController::class, 'getPackageStats']);

        Route::group(['prefix' => 'listings'], function () {
            Route::get('/', [ListingController::class, 'index']);
            Route::get('/{listing}', [ListingController::class, 'show']);
            Route::post('/', [ListingController::class, 'store']);
            Route::post('/{listing}', [ListingController::class, 'update']);
            Route::post('/{listing}/status', [ListingController::class, 'changeStatus']);
            Route::post('/{listing}/top', [ListingController::class, 'addTop']);
            Route::delete('/{listing}', [ListingController::class, 'destroy']);
            Route::delete('/{listing}/photos/{photo}', [ListingController::class, 'deletePhoto']);

        });

        Route::post('packages/{package}/buy', [PackageController::class, 'buy']);
        Route::post('advertisements/{advertisement}/buy', [AdvertisementController::class, 'buy']);
        Route::get('payments/{order}/status', [OrderController::class, 'status']);
        Route::get('payments/history', [OrderController::class, 'history']);
    });
});
