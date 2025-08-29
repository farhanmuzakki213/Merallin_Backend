<?php

use App\Http\Controllers\NotificationController;
use App\Livewire\AttendanceTable;
use App\Livewire\BbmKendaraanTable;
use App\Livewire\Dashboard;
use App\Livewire\IzinTable;
use App\Livewire\LemburTable;
use App\Livewire\Profile;
use App\Livewire\TripTable;
use App\Livewire\UserTable;
use App\Livewire\VehicleLocationTable;
use App\Livewire\VehicleTable;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)
        ->middleware(['verified'])
        ->name('dashboard');

    Route::post('/push-subscribe', function (Request $request) {
        $user = auth()->user();
        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json(['success' => true], 200);
    });

    Route::get('profile', Profile::class)
        ->name('profile');

    Route::get('/users/table', UserTable::class)->name('users.table');

    Route::get('/attendances/table', AttendanceTable::class)->name('attendances.table');

    Route::get('/trips/table', TripTable::class)->name('trips.table');

    Route::get('/izin-karyawan/table', IzinTable::class)->name('izin.table');

    Route::get('/lembur/table', LemburTable::class)->middleware(['auth'])->name('lembur.table');

    Route::get('/vehicles/table', VehicleTable::class)->name('vehicles.table');

    Route::get('/vehicle-locations/table', VehicleLocationTable::class)->name('vehicleLocations.table');

    Route::get('/bbm-kendaraan/table', BbmKendaraanTable::class)->name('bbm.table');
});

require __DIR__ . '/auth.php';
