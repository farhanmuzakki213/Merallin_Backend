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
    public function startTrip(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'license_plate' => 'required|string|max:20',
                'start_km' => 'required|integer',
                'start_photo' => 'required|image|max:8192',
                'delivery_letter' => 'required|image|max:8192',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = $request->user();

            // Cek apakah ada trip yang masih aktif
            $activeTrip = Trip::where('user_id', $user->id)->whereNull('ended_at')->first();
            if ($activeTrip) {
                return response()->json(['message' => 'Anda masih memiliki perjalanan yang aktif.'], 409); // 409 Conflict
            }

            // Simpan file-file
            $startPhotoPath = $request->file('start_photo')->store('public/trip_photos');
            $deliveryLetterPath = $request->file('delivery_letter')->store('public/delivery_letters');

            // Buat record trip baru
            $trip = Trip::create([
                'user_id' => $user->id,
                'project_name' => $validated['project_name'],
                'license_plate' => $validated['license_plate'],
                'start_km' => $validated['start_km'],
                'start_photo_path' => $startPhotoPath,
                'delivery_letter_path' => $deliveryLetterPath,
                'start_latitude' => $validated['latitude'],
                'start_longitude' => $validated['longitude'],
                'status' => 'pengajuan', // Default status
            ]);

            return response()->json(['message' => 'Perjalanan berhasil dimulai.', 'trip' => $trip], 201);
        } catch (ValidationException  $e) {
            throw $e;
        } catch (Throwable $th) {
            // Tangkap semua error LAINNYA
            Log::error('Start Trip Error: ' . $th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
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
            $trips = $user->trips()->latest()->get()->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'project_name' => $trip->project_name,
                    'license_plate' => $trip->license_plate,
                    'status' => $trip->status,
                    'start_km' => $trip->start_km,
                    'end_km' => $trip->end_km,
                    'started_at' => $trip->started_at->toDateTimeString(),
                    'ended_at' => $trip->ended_at?->toDateTimeString(),
                    'start_photo_url' => Storage::url($trip->start_photo_path),
                    'end_photo_url' => $trip->end_photo_path ? Storage::url($trip->end_photo_path) : null,
                ];
            });

            return response()->json($trips);
        } catch (Throwable $th) {
            Log::error('Get Trips Error: ' . $th->getMessage());
            return response()->json(['message' => 'Gagal mengambil data perjalanan.'], 500);
        }
    }
}
