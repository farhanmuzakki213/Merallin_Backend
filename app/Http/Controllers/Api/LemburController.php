<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\LemburResource;
use App\Http\Requests\StoreLemburRequest;
use App\Helpers\DateHelper;

class LemburController extends Controller
{
    /**
     * Menampilkan daftar data lembur milik user yang sedang login.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $lembur = $user->lemburs()->latest()->get();

            if ($lembur->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data lembur yang ditemukan.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $lembur,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching lembur data: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data lembur.'], 500);
        }
    }

    /**
     * Menyimpan data lembur baru.
     */
    public function store(StoreLemburRequest $request): JsonResponse
    {
        try {
            // Validasi sudah otomatis ditangani oleh StoreLemburRequest

            // Mengambil data yang sudah tervalidasi
            $validatedData = $request->validated();

            // Menambahkan user_id dari user yang sedang login
            $validatedData['user_id'] = Auth::id();

            // Membuat data lembur baru
            $lembur = Lembur::create($validatedData);

            // Mengembalikan respons sukses dengan data yang baru dibuat
            return response()->json([
                'message' => 'Data lembur berhasil ditambahkan.',
                'data' => new LemburResource($lembur)
            ], 201); // 201 Created

        } catch (\Illuminate\Validation\ValidationException $e) {
            // This is already handled by the form request, but as a fallback
            return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error storing lembur data: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data lembur.'], 500);
        }
    }

    public function show(Lembur $lembur): JsonResponse
    {
        // Keamanan: Pastikan pengguna yang meminta adalah pemilik data lembur
        if (Auth::id() !== $lembur->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Siapkan data dasar untuk respons
        $responseData = [
            'status_final' => $lembur->status_lembur,
            'alasan_penolakan' => $lembur->alasan,
            'file_final_url' => null // Default null
        ];

        if ($lembur->status_lembur === 'Diterima' && !empty($lembur->file_path)) {
            $responseData['file_final_url'] = $lembur->file_url;
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    public function clockIn(Request $request, Lembur $lembur): JsonResponse
    {
        // Keamanan: Pastikan user adalah pemilik lembur
        if (Auth::id() !== $lembur->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ===== VALIDASI 1: KUOTA LEMBUR MINGGUAN =====
        $user = Auth::user();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $completedLembursThisWeek = Lembur::where('user_id', $user->id)
            ->whereNotNull('jam_selesai_aktual')
            ->whereBetween('tanggal_lembur', [$startOfWeek, $endOfWeek])
            ->get();

        $totalSecondsThisWeek = 0;
        foreach ($completedLembursThisWeek as $completedLembur) {
            $startTime = Carbon::parse($completedLembur->jam_mulai_aktual);
            $endTime = Carbon::parse($completedLembur->jam_selesai_aktual);
            $totalSecondsThisWeek += $startTime->diffInSeconds($endTime);
        }

        $totalHoursThisWeek = $totalSecondsThisWeek / 3600;

        if ($totalHoursThisWeek >= 20) {
            return response()->json(['message' => 'Gagal: Anda telah melebihi kuota lembur 20 jam minggu ini.'], 422);
        }

        // ===== VALIDASI 2: WAKTU DAN TANGGAL MULAI LEMBUR =====
        $now = now();
        $scheduledDate = Carbon::parse($lembur->tanggal_lembur);
        $scheduledStartTime = Carbon::parse($lembur->tanggal_lembur . ' ' . $lembur->mulai_jam_lembur);

        // Cek apakah hari ini adalah tanggal lembur yang dijadwalkalkan
        if ($now->toDateString() !== $scheduledDate->toDateString()) {
            // FORMAT TANGGAL SEBELUM DIKIRIM KE JSON
            $formattedScheduledDate = $scheduledDate->translatedFormat('d F Y');
            return response()->json([
                'message' => 'Gagal: Anda hanya bisa memulai lembur pada tanggal yang dijadwalkan (' . $formattedScheduledDate . ').'
            ], 422);
        }

        // Cek apakah sudah melewati jam mulai yang dijadwalkan
        if ($now->isBefore($scheduledStartTime)) {
            // FORMAT WAKTU SEBELUM DIKIRIM KE JSON
            $formattedScheduledTime = $scheduledStartTime->format('H:i');
            return response()->json([
                'message' => 'Gagal: Anda belum bisa memulai lembur. Jadwal mulai: ' . $formattedScheduledTime . ' WIB.'
            ], 422);
        }

        // Validasi: Pastikan statusnya Diterima dan belum pernah clock-in
        if ($lembur->status_lembur !== 'Diterima' || $lembur->jam_mulai_aktual !== null) {
            return response()->json(['message' => 'Lembur belum bisa dimulai.'], 422);
        }

        $request->validate([
            'foto_mulai' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Simpan foto
        $path = $request->file('foto_mulai')->store('lembur_proofs', 'public');

        // Update data di database
        $lembur->update([
            'jam_mulai_aktual' => now(),
            'foto_mulai_path' => $path,
            'lokasi_mulai' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clock-in lembur berhasil direkam.',
            'data' => new LemburResource($lembur->fresh()),
        ]);
    }

    /**
     * PENAMBAHAN: Merekam data saat pengguna menyelesaikan lembur.
     */
    public function clockOut(Request $request, Lembur $lembur): JsonResponse
    {
        if ($lembur->jam_selesai_aktual) {
            return response()->json(['message' => 'Anda sudah melakukan clock-out untuk lembur ini.'], 400);
        }

        if (!$lembur->jam_mulai_aktual) {
            return response()->json(['message' => 'Clock-in belum dilakukan. Tidak bisa melakukan clock-out.'], 400);
        }

        $request->validate([
            'foto_selesai' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $path = $request->file('foto_selesai')->store('lembur_proofs', 'public');

        $gajiPokok = $lembur->user->gaji_pokok;
        $jamMulaiAktual = Carbon::parse($lembur->jam_mulai_aktual);
        $jamSelesaiAktual = now();
        $jamMulaiAktual = Carbon::parse($lembur->jam_mulai_aktual);
        $durasiAktualDetik = $jamMulaiAktual->diffInSeconds($jamSelesaiAktual);

        $jamMulaiRencana = Carbon::parse($lembur->tanggal_lembur . ' ' . $lembur->mulai_jam_lembur);
        $jamSelesaiRencana = Carbon::parse($lembur->tanggal_lembur . ' ' . $lembur->selesai_jam_lembur);
        if ($jamSelesaiRencana->lt($jamMulaiRencana)) {
            $jamSelesaiRencana->addDay();
        }
        $durasiRencanaDetik = $jamMulaiRencana->diffInSeconds($jamSelesaiRencana);

        $durasiUntukPerhitunganDetik = min($durasiAktualDetik, $durasiRencanaDetik);

        $totalJamLembur = round($durasiUntukPerhitunganDetik / 3600, 2);
        if ($totalJamLembur < 0) {
            $totalJamLembur = 0;
        }

        if ($gajiPokok <= 0) {
            return response()->json([
                'error' => 'Gaji Pokok Tidak Ditemukan',
                'message' => 'Perhitungan gaji lembur tidak dapat dilakukan karena data gaji pokok tidak valid.'
            ], 422);
        }

        $tanggalLembur = Carbon::parse($lembur->tanggal_lembur);
        $isHariLibur = DateHelper::isNationalHoliday($tanggalLembur) || $tanggalLembur->isWeekend();

        // VALIDASI 4: Cek batasan jam lembur mingguan
        $startOfWeek = $tanggalLembur->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $tanggalLembur->copy()->endOfWeek(Carbon::SUNDAY);

        $totalJamMingguanSebelumnya = Lembur::where('user_id', $lembur->user_id)
            ->where('status_lembur', 'Diterima')
            ->where('uuid', '!=', $lembur->uuid)
            ->whereBetween('tanggal_lembur', [$startOfWeek, $endOfWeek])
            ->sum('total_jam');

        $lembur->jam_selesai_aktual = $jamSelesaiAktual;
        $lembur->foto_selesai_path = $path;
        $lembur->lokasi_selesai = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];
        $lembur->total_jam = $totalJamLembur;
        $pengali = $isHariLibur ? 2 : 1.5;
        $gajiPerJam = $gajiPokok / 173;
        $gajiLembur = $gajiPerJam * $pengali * $totalJamLembur;
        $lembur->gaji_lembur = round($gajiLembur, 2);

        $lembur->save();

        return response()->json([
            'success' => true,
            'message' => 'Clock-out lembur berhasil direkam.',
            'data' => new LemburResource($lembur->fresh()),
        ]);
    }
}
