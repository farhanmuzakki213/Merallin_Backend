<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class TripController extends Controller
{
    public function requestTrip(Request $request)
    {
        try {
            // 1. Validasi input dari form pop-up
            // Menyesuaikan validasi dengan data yang dibutuhkan untuk pengajuan.
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'origin'       => 'required|string|max:255',
                'destination'  => 'required|string|max:255',
            ]);

            $user = $request->user();

            // 2. Cek apakah driver masih memiliki trip yang aktif (belum selesai)
            // Logika ini sudah benar untuk mencegah driver membuat trip baru jika masih ada yang aktif.
            // Sebuah trip dianggap aktif jika 'ended_at' nya masih kosong (NULL).
            $activeTrip = Trip::where('user_id', $user->id)->whereNull('started_at')->first();
            if ($activeTrip) {
                return response()->json([
                    'message' => 'Anda masih memiliki perjalanan yang aktif dan belum diselesaikan.'
                ], 409); // 409 Conflict
            }

            // 3. Buat record trip baru dengan status 'pengajuan'
            // Menghapus field yang tidak perlu seperti license_plate, km, foto, dan koordinat.
            $trip = Trip::create([
                'user_id'      => $user->id, // Diambil dari user yang login
                'project_name' => $validated['project_name'],
                'origin'       => $validated['origin'],
                'destination'  => $validated['destination'],
                'status_trip'  => 'pengajuan', // Status diatur secara otomatis
            ]);

            // 4. Beri respons sukses
            return response()->json([
                'message' => 'Pengajuan perjalanan berhasil dibuat dan menunggu persetujuan.',
                'trip'    => $trip
            ], 201); // 201 Created

        } catch (ValidationException $e) {
            // Jika validasi gagal, kembalikan error validasi
            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $th) {
            // Tangkap semua error lainnya untuk mencegah aplikasi crash
            Log::error('Request Trip Error: ' . $th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    // Fungsi untuk mengakhiri perjalanan
    public function endTrip(Request $request, Trip $trip)
    {
        try {
            // Pastikan driver hanya bisa mengakhiri trip miliknya sendiri
            if ($request->user()->id !== $trip->user_id) {
                return response()->json(['message' => 'Tidak diizinkan.'], 403);
            }

            $validated = $request->validate([
                'end_km' => 'required|integer|gte:start_km',
                'end_photo' => 'required|image|max:8192',
                'end_delivery_letter' => 'required_if:status,bongkar|image|max:8192',
                'status' => 'required|in:tiba,bongkar',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // Simpan foto selesai
            $trip->end_photo_path = $request->file('end_photo')->store('public/trip_photos');

            // Simpan bukti bongkar jika ada
            if ($request->hasFile('end_delivery_letter')) {
                $trip->end_delivery_letter_path = $request->file('end_delivery_letter')->store('public/delivery_letters');
            }

            // Update data trip
            $trip->end_km = $validated['end_km'];
            $trip->status = $validated['status'];
            $trip->end_latitude = $validated['latitude'];
            $trip->end_longitude = $validated['longitude'];
            $trip->ended_at = now();
            $trip->save();

            return response()->json(['message' => 'Perjalanan berhasil diselesaikan.', 'trip' => $trip]);
        } catch (ValidationException  $e) {
            throw $e;
        } catch (Throwable $th) {
            Log::error('End Trip Error: ' . $th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }

    public function getTrips(Request $request)
    {
        try {
            $user = $request->user();

            // Ambil semua trip milik user, urutkan dari yang terbaru
            $trips = $user->trips()->with('user')->latest()->get()->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'project_name' => $trip->project_name,
                    'license_plate' => $trip->license_plate,
                    'status_trip' => $trip->status_trip,
                    'status_lokasi' => $trip->status_lokasi,
                    'status_muatan' => $trip->status_muatan,
                    'origin' => $trip->origin,
                    'destination' => $trip->destination,
                    'start_km' => $trip->start_km,
                    'end_km' => $trip->end_km,
                    'started_at' => $trip->started_at->toDateTimeString(),
                    'ended_at' => $trip->ended_at?->toDateTimeString(),
                    'start_photo_url' => Storage::url($trip->start_photo_path),
                    'end_photo_url' => $trip->end_photo_path ? Storage::url($trip->end_photo_path) : null,
                    'user' => [
                        'name' => $trip->user->name,
                    ]
                ];
            });

            return response()->json($trips);
        } catch (Throwable $th) {
            Log::error('Get Trips Error: ' . $th->getMessage());
            return response()->json(['message' => 'Gagal mengambil data perjalanan.'], 500);
        }
    }
}
