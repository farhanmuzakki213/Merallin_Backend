<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BbmKendaraan;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    /**
     * Menampilkan daftar semua kendaraan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function index()
    // {
    //     $user = Auth::user();

    //     // Pengecekan 1: Cek apakah user yang login sedang memiliki tugas aktif.
    //     $userHasActiveTrip = Trip::where('user_id', $user->id)
    //         ->whereIn('status_trip', ['proses', 'verifikasi gambar', 'revisi gambar'])
    //         ->exists();

    //     $userHasActiveLocation = VehicleLocation::where('user_id', $user->id)
    //         ->where('status_vehicle_location', '!=', 'selesai')
    //         ->exists();

    //     $userHasActiveTask = $userHasActiveTrip || $userHasActiveLocation;

    //     // Pengecekan 2: Cari semua kendaraan yang sedang digunakan di trip atau trip geser yang aktif.
    //     $vehicleIdsInActiveTrips = Trip::whereIn('status_trip', ['proses', 'verifikasi gambar', 'revisi gambar'])
    //         ->whereNotNull('vehicle_id')
    //         ->pluck('vehicle_id');

    //     $vehicleIdsInActiveLocations = VehicleLocation::where('status_vehicle_location', '!=', 'selesai')
    //         ->whereNotNull('vehicle_id')
    //         ->pluck('vehicle_id');

    //     // Gabungkan semua ID kendaraan yang sedang tidak tersedia (digunakan).
    //     $unavailableVehicleIds = $vehicleIdsInActiveTrips->merge($vehicleIdsInActiveLocations)->unique();

    //     // Pengecekan 3: Ambil hanya kendaraan yang TIDAK TERMASUK dalam daftar yang sedang digunakan.
    //     $availableVehicles = Vehicle::whereNotIn('id', $unavailableVehicleIds)
    //         ->latest()
    //         ->get();

    //     // Kembalikan response dalam format JSON
    //     return response()->json([
    //         'user_has_active_task' => $userHasActiveTask,
    //         'vehicles' => $availableVehicles,
    //     ]);
    // }

    public function getAvailableVehicles()
    {
        try {
            $busyOnTrips = Trip::where('status_trip', '!=', 'selesai')
                ->pluck('vehicle_id');

            $busyOnLocations = VehicleLocation::where('status_vehicle_location', '!=', 'selesai')
                ->pluck('vehicle_id');

            $busyOnBbm = BbmKendaraan::where('status_bbm_kendaraan', '!=', 'selesai')
                ->pluck('vehicle_id');

            $busyVehicleIds = $busyOnTrips
                ->merge($busyOnLocations)
                ->merge($busyOnBbm)
                ->unique();

            $availableVehicles = Vehicle::whereNotIn('id', $busyVehicleIds)->latest()->get();

            return response()->json($availableVehicles);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengambil kendaraan tersedia: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
    public function index()
    {
        $vehicles = Vehicle::latest()->get();

        return response()->json($vehicles);
    }
}
