<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceController extends Controller
{

    public function clockIn(Request $request)
    {
        Log::info('Menerima permintaan absensi...');
        try {
            $request->validate([
                'photo' => 'required|image',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'tipe_absensi' => 'required|in:datang,pulang',
                'status_absensi' => 'required|in:Tepat waktu,Terlambat',
            ]);

            Log::info('Validasi berhasil.');
            $user = $request->user();

            // Validasi keamanan
            if ($request->is_mocked) {
                Log::warning('Terdeteksi lokasi palsu dari user: ' . $user->id);
                return response()->json(['message' => 'Terdeteksi menggunakan lokasi palsu.'], 403);
            }

            // Simpan foto
            $path = $request->file('photo')->store('public/attendance_photos');
            Log::info('Foto berhasil disimpan di: ' . $path);

            // Simpan data absensi
            $user->attendances()->create([
                'photo_path' => $path,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tipe_absensi' => $request->tipe_absensi,
                'status_absensi' => $request->status_absensi,
            ]);
            Log::info('Data absensi berhasil disimpan untuk user: ' . $user->id);

            return response()->json(['message' => 'Absensi berhasil direkam.']);
        } catch (ValidationException $e) {
            // Jika validasi gagal, kirim respons error 422
            Log::error('Gagal validasi: ', $e->errors());
            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $th) {
            // Jika terjadi error lain (misal: gagal simpan file, masalah database, dll.)
            // Log errornya untuk debugging di server
            Log::error('Error Absensi: ' . $th->getMessage());

            // Kirim respons error 500 ke frontend
            return response()->json([
                'message' => 'Terjadi kesalahan di server. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $history = $request->user()->attendances()
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'photo_url' => Storage::url($item->photo_path),
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'tipe_absensi' => $item->tipe_absensi,
                    'status_absensi' => $item->status_absensi,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

        return response()->json($history);
    }
}
