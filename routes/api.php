<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $roles = $user->getRoleNames();

    return response()->json([
        'user' => $user,
        'roles' => $roles
    ]);
});
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');
Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');
Route::post('/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
