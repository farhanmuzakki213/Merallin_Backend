<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\AzureFaceService; // Import service
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceController extends Controller
{
    protected $azureFaceService;

    // public function __construct(AzureFaceService $azureFaceService)
    // {
    //     $this->azureFaceService = $azureFaceService;
    // }

    public function clockIn(Request $request)
    {
        Log::info('Menerima permintaan absensi...');
        try {
            $request->validate([
                'photo' => 'required|image|max:2048',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'is_mocked' => 'required|boolean',
                // 'azure_person_id' => 'required|string', // Terima personId dari Flutter
            ]);

            Log::info('Validasi berhasil.');
            $user = $request->user();

            // Validasi keamanan
            if ($request->is_mocked) {
                Log::warning('Terdeteksi lokasi palsu dari user: ' . $user->id);
                return response()->json(['message' => 'Terdeteksi menggunakan lokasi palsu.'], 403);
            }

            // Verifikasi bahwa personId yang dikirim dari Flutter cocok dengan yang ada di database
            // if ($user->azure_person_id !== $request->azure_person_id) {
            //     return response()->json(['message' => 'Verifikasi wajah gagal. Data tidak cocok.'], 403);
            // }

            // Simpan foto
            $path = $request->file('photo')->store('public/attendance_photos');
            Log::info('Foto berhasil disimpan di: ' . $path);

            // Simpan data absensi
            $user->attendances()->create([
                'photo_path' => $path,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_mocked' => $request->is_mocked,
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
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

        return response()->json($history);
    }
}
