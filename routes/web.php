<?php

use App\Livewire\AttendanceTable;
use App\Livewire\Dashboard;
use App\Livewire\IzinTable;
use App\Livewire\LemburTable;
use App\Livewire\Profile;
use App\Livewire\TripTable;
use App\Livewire\UserTable;
use App\Livewire\VehicleTable;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)
        ->middleware(['verified'])
        ->name('dashboard');

    Route::get('profile', Profile::class)
        ->name('profile');

    Route::get('/users/table', UserTable::class)->name('users.table');

    Route::get('/attendances/table', AttendanceTable::class)->name('attendances.table');

    Route::get('/trips/table', TripTable::class)->name('trips.table');

    Route::get('/izin-karyawan', IzinTable::class)->name('izin.table');

    Route::get('/lembur', LemburTable::class)->middleware(['auth'])->name('lembur.table');

    Route::get('/vehicles', VehicleTable::class)->name('vehicles.table');
});

require __DIR__.'/auth.php';
