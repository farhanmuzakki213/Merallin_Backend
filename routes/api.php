<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute baru untuk registrasi wajah dan absensi
    Route::post('/user/register-face', [AuthController::class, 'registerFace']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
});
