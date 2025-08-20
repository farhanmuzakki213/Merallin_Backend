<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IzinController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\ProfileController;
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

    // Endpoint umum
    Route::get('/trips/{trip}', [TripController::class, 'show']);

    // --- RUTE UNTUK DRIVER ---
    Route::prefix('driver')->group(function () {
        // Melihat daftar trip (tersedia & milik sendiri)
        Route::get('/trips', [TripController::class, 'indexDriver']);

        // Membuat trip sendiri
        Route::post('/trips', [TripController::class, 'storeByDriver']);

        // Mengambil trip dari admin
        Route::post('/trips/{trip}/accept', [TripController::class, 'acceptTrip']);

        // Update data perjalanan (menggunakan POST karena ada file upload)
        Route::post('/trips/{trip}/start', [TripController::class, 'updateStart']);
        Route::post('/trips/{trip}/at-loading', [TripController::class, 'updateAtLoadingPoint']);
        Route::post('/trips/{trip}/finish-loading', [TripController::class, 'finishLoading']);
        Route::post('/trips/{trip}/after-loading', [TripController::class, 'updateAfterLoading']);
        Route::post('/trips/{trip}/at-unloading', [TripController::class, 'updateAtUnloadingPoint']);
        Route::post('/trips/{trip}/finish-unloading', [TripController::class, 'finishUnloading']);
        Route::post('/trips/{trip}/finish', [TripController::class, 'updateFinish']);

    });
    Route::apiResource('izin', IzinController::class)->only(['index', 'store']);
    Route::apiResource('lembur', LemburController::class)->only(['index', 'store']);

});
