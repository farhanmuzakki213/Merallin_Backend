<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverLocationController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/user/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/user/password', [ProfileController::class, 'updatePassword']);

    // Rute untuk absensi karyawan
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/status-today', [AttendanceController::class, 'statusToday']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);

    // Rute untuk Absensi Driver
    Route::post('/requestTrip', [TripController::class, 'startTrip']);
    Route::post('/trips/{trip}/end', [TripController::class, 'endTrip']);
    Route::get('/trips/history', [TripController::class, 'getTrips']);
});
