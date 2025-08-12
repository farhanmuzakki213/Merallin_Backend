<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AttendanceController extends Controller
{

    /**
     * Mengecek status absensi pengguna untuk hari ini.
     */
    public function statusToday(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $hasClockedIn = Attendance::where('user_id', $user->id)
            ->where('tipe_absensi', 'datang')
            ->whereDate('created_at', $today)
            ->exists();

        $hasClockedOut = Attendance::where('user_id', $user->id)
            ->where('tipe_absensi', 'pulang')
            ->whereDate('created_at', $today)
            ->exists();

        return response()->json([
            'has_clocked_in' => $hasClockedIn,
            'has_clocked_out' => $hasClockedOut,
        ]);
    }

    /**
     * Melakukan absensi datang (clock-in).
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $today = Carbon::today();

        // VALIDASI: Cek apakah sudah absen datang hari ini
        $alreadyClockedIn = Attendance::where('user_id', $user->id)
            ->where('tipe_absensi', 'datang')
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadyClockedIn) {
            return response()->json(['message' => 'Anda sudah melakukan absensi datang hari ini.'], 409); // 409 Conflict
        }

        return $this->createAttendance($request, 'datang');
    }

    /**
     * Melakukan absensi pulang (clock-out).
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $today = Carbon::today();

        // VALIDASI 1: Cek apakah sudah absen datang hari ini
        $hasClockedIn = Attendance::where('user_id', $user->id)
            ->where('tipe_absensi', 'datang')
            ->whereDate('created_at', $today)
            ->exists();

        if (!$hasClockedIn) {
            return response()->json(['message' => 'Anda harus melakukan absensi datang terlebih dahulu.'], 422);
        }

        // VALIDASI 2: Cek apakah sudah absen pulang hari ini
        $alreadyClockedOut = Attendance::where('user_id', $user->id)
            ->where('tipe_absensi', 'pulang')
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadyClockedOut) {
            return response()->json(['message' => 'Anda sudah melakukan absensi pulang hari ini.'], 409);
        }

        return $this->createAttendance($request, 'pulang');
    }

    /**
     * Fungsi helper untuk membuat record absensi.
     */
    private function createAttendance(Request $request, string $tipe)
    {
        $photoPath = $request->file('photo')->store('public/photos');

        $status = 'Tepat waktu';
        $now = now();
        if ($tipe == 'datang' && $now->hour > 8) {
            $status = 'Terlambat';
        }

        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'photo_path' => $photoPath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'tipe_absensi' => $tipe,
            'status_absensi' => $status,
        ]);

        return response()->json([
            'message' => 'Absensi ' . ucfirst($tipe) . ' berhasil direkam!',
            'data' => $attendance
        ], 201);
    }

    public function history(Request $request)
    {
        $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        $query = $request->user()->attendances();

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }
        $history = $query->latest()
            ->get()
            ->map(function ($item) use ($request){
                // Siapkan fallback jika relasi user terputus
                $userName = $request->user()->name;
                return [
                    'id' => $item->id,
                    'namaUser' => $userName,
                    'photoUrl' => Storage::url($item->photo_path),
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'tipeAbsensi' => $item->tipe_absensi,
                    'statusAbsensi' => $item->status_absensi,
                    'createdAt' => $item->created_at->toIso8601String(),
                ];
            });
        return response()->json($history);
    }
}
