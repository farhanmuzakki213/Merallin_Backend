<?php

use App\Livewire\Dashboard;
use App\Livewire\Profile;
use App\Livewire\UserTable;
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

    Route::get('/tables', UserTable::class)->name('tables');
});

require __DIR__.'/auth.php';
