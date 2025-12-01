<?php

use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware('auth:admin')->group(function () {
    Route::get('/', fn()=> 'Admin Dashboard')->name('admin.dashboard');
});
