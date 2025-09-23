<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IzinController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SalarySlipController;
use App\Http\Controllers\Api\BbmKendaraanController;
use App\Http\Controllers\Api\IdCardController;
use App\Http\Controllers\Api\SharedFileController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\VehicleLocationController;


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

    // Rute untuk slip gaji
    Route::get('/salary-slips', [SalarySlipController::class, 'index']);
    Route::get('/share/slip/{uuid}', [SharedFileController::class, 'serveSalarySlip'])->name('salary-slips.share');

    Route::get('/user/id-card/{uuid}', [IdCardController::class, 'serveIdCard'])->name('user.id-card.share');

    Route::prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/', [LemburController::class, 'index'])->name('index'); // nama menjadi lembur.index
        Route::post('/', [LemburController::class, 'store'])->name('store'); // nama menjadi lembur.store
        Route::get('/{lembur:uuid}', [LemburController::class, 'show'])->name('show'); // nama menjadi lembur.show

        // --- PENAMBAHAN: Rute untuk clock-in dan clock-out lembur ---
        Route::post('/{lembur:uuid}/clock-in', [LemburController::class, 'clockIn'])->name('clockIn');
        Route::post('/{lembur:uuid}/clock-out', [LemburController::class, 'clockOut'])->name('clockOut');
    });
    Route::get('/share/lembur/{uuid}', [SharedFileController::class, 'serveLemburFile'])->name('lembur.share');

    // Endpoint umum
    Route::get('/trips/{trip}', [TripController::class, 'show']);

    // --- RUTE UNTUK DRIVER ---
    Route::prefix('driver')->group(function () {

        Route::get('/trips', [TripController::class, 'indexDriver']);
        // Mengambil trip dari admin
        Route::post('/trips/{trip}/accept', [TripController::class, 'acceptTrip']);

        // Update data perjalanan (menggunakan POST karena ada file upload)
        Route::post('/trips/{trip}/start', [TripController::class, 'updateStart']);
        Route::post('/trips/{trip}/at-loading', [TripController::class, 'updateAtLoadingPoint']);
        Route::post('/trips/{trip}/update-kedatangan-muat', [TripController::class, 'updateKedatanganMuat']);
        Route::post('/trips/{trip}/update-proses-muat', [TripController::class, 'updateProsesMuat']);
        Route::post('/trips/{trip}/upload-selesai-muat', [TripController::class, 'uploadSelesaiMuat']);
        Route::post('/trips/{trip}/at-unloading', [TripController::class, 'updateAtUnloadingPoint']);
        Route::post('/trips/{trip}/update-kedatangan-bongkar', [TripController::class, 'updateKedatanganBongkar']);
        Route::post('/trips/{trip}/update-proses-bongkar', [TripController::class, 'updateProsesBongkar']);
        Route::post('/trips/{trip}/update-selesai-bongkar', [TripController::class, 'updateSelesaiBongkar']);

        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/available-vehicles', [VehicleController::class, 'getAvailableVehicles']);

        Route::get('/bbm_kendaraan', [BbmKendaraanController::class, 'index']);
        Route::post('/bbm_kendaraan', [BbmKendaraanController::class, 'store']);
        Route::get('/bbm_kendaraan/{bbmKendaraan}', [BbmKendaraanController::class, 'show']);
        Route::post('/bbm_kendaraan/{bbmKendaraan}/upload-start-km', [BbmKendaraanController::class, 'uploadStartKm']);
        Route::post('/bbm_kendaraan/{bbmKendaraan}/finish-filling', [BbmKendaraanController::class, 'finishFilling']);
        Route::post('/bbm_kendaraan/{bbmKendaraan}/upload-end-km-nota', [BbmKendaraanController::class, 'uploadEndKmAndNota']);

        Route::get('/vehicle-locations', [VehicleLocationController::class, 'index']);
        Route::post('/vehicle-locations', [VehicleLocationController::class, 'store']);
        Route::get('/vehicle-locations/{vehicleLocation}', [VehicleLocationController::class, 'show']);
        Route::post('/vehicle-locations/{vehicleLocation}/upload-standby-start', [VehicleLocationController::class, 'uploadStandbyAndStartKm']);
        Route::post('/vehicle-locations/{vehicleLocation}/arrive', [VehicleLocationController::class, 'arriveAtLocation']);
        Route::post('/vehicle-locations/{vehicleLocation}/upload-end', [VehicleLocationController::class, 'uploadEndKm']);
    });
    Route::apiResource('izin', IzinController::class)->only(['index', 'store']);
    // Route::apiResource('lembur', LemburController::class)->only(['index', 'store']);
});
