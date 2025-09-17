<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\EscalateBbmVerificationJob;
use App\Models\BbmKendaraan;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Notifications\BbmPhotoVerificationRequired;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BbmKendaraanController extends Controller
{
    private function triggerVerificationProcess(BbmKendaraan $bbm, string $photoType, string $photoDisplayName, string $publicPhotoUrl)
    {
        try {
            $admins = User::role('admin')->whereHas('pushSubscriptions')->get();
            $vehicleInfo = $bbm->vehicle->license_plate ?? 'N/A';

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new BbmPhotoVerificationRequired(
                    $bbm,
                    $photoDisplayName,
                    $vehicleInfo,
                    $publicPhotoUrl
                ));
            }

            $statusField = $photoType . '_status';

            EscalateBbmVerificationJob::dispatch($bbm, $photoDisplayName, $vehicleInfo, $publicPhotoUrl, 'manager', $statusField)->delay(now()->addMinutes(1));
            EscalateBbmVerificationJob::dispatch($bbm, $photoDisplayName, $vehicleInfo, $publicPhotoUrl, 'direksi', $statusField)->delay(now()->addMinutes(2));
        } catch (\Exception $e) {
            Log::error('Gagal memicu proses verifikasi BBM: ' . $e->getMessage());
        }
    }

    private function getFieldsToReset(BbmKendaraan $bbm, array $photoTypes): array
    {
        $statusMap = [
            'start_km_photo' => 'start_km_photo_status',
            'end_km_photo' => 'end_km_photo_status',
            'nota_pengisian_photo' => 'nota_pengisian_photo_status',
        ];
        $updates = [];
        $shouldResetOverallStatus = false;
        foreach ($photoTypes as $type) {
            $statusField = $statusMap[$type] ?? null;
            if ($statusField && $bbm->{$statusField} === 'rejected') {
                $updates[$statusField] = 'pending';
                $shouldResetOverallStatus = true;
            }
        }
        if ($shouldResetOverallStatus) {
            $updates['status_bbm_kendaraan'] = 'verifikasi gambar';
        }
        return $updates;
    }

    private function generateUniqueFileName($file): string
    {
        $userName = Str::slug(Auth::user()->name, '-');
        $timestamp = now()->format('Ymd_His');
        $uniqueId = uniqid();
        $extension = $file->getClientOriginalExtension();
        return "{$userName}_{$timestamp}_{$uniqueId}.{$extension}";
    }

    public function index()
    {
        $history = BbmKendaraan::with('vehicle')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        return response()->json($history);
    }

    public function show(BbmKendaraan $bbmKendaraan)
    {
        return response()->json($bbmKendaraan->load('vehicle', 'user'));
    }

    public function store(Request $request)
    {
        $busyOnTrips = Trip::where('status_trip', '!=', 'selesai')
            ->whereNotNull('vehicle_id')
            ->pluck('vehicle_id');

        $busyOnLocations = VehicleLocation::where('status_vehicle_location', '!=', 'selesai')
            ->whereNotNull('vehicle_id')
            ->pluck('vehicle_id');

        $busyOnBbm = BbmKendaraan::where('status_bbm_kendaraan', '!=', 'selesai')
            ->whereNotNull('vehicle_id')
            ->pluck('vehicle_id');

        $busyVehicleIds = $busyOnTrips
            ->merge($busyOnLocations)
            ->merge($busyOnBbm)
            ->unique();

        $availableVehicles = Vehicle::whereNotIn('id', $busyVehicleIds)->latest()->get();

        $availableVehicleIds = $availableVehicles->pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'vehicle_id' => [
                'required',
                Rule::in($availableVehicleIds)
            ],
        ]);
        if ($validator->fails()) {
            if ($validator->errors()->has('vehicle_id') && !in_array($request->vehicle_id, $availableVehicleIds)) {
                return response()->json(['message' => 'Kendaraan yang dipilih tidak tersedia atau sedang digunakan.'], 422);
            }
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $bbm = BbmKendaraan::create([
            'user_id' => Auth::id(),
            'vehicle_id' => $request->vehicle_id,
            'status_pengisian' => 'sedang antri',
        ]);

        return response()->json(['message' => 'Data awal BBM berhasil dibuat.', 'data' => $bbm], 201);
    }

    public function uploadStartKm(Request $request, BbmKendaraan $bbmKendaraan)
    {
        $validator = Validator::make($request->all(), [
            'start_km_photo' => 'required|image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $bbmKendaraan->fill($this->getFieldsToReset($bbmKendaraan, ['start_km_photo']));

            if ($request->hasFile('start_km_photo')) {
                if ($bbmKendaraan->start_km_photo_path) Storage::disk('public')->delete($bbmKendaraan->start_km_photo_path);

                $file = $request->file('start_km_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('bbm_photos/start_km', $fileName, 'public');
                $bbmKendaraan->start_km_photo_path = $path;

                $this->triggerVerificationProcess($bbmKendaraan, 'start_km_photo', 'Foto KM Awal BBM', Storage::url($path));
            }
            $bbmKendaraan->status_pengisian = 'sedang isi bbm';
            $bbmKendaraan->status_bbm_kendaraan = 'verifikasi gambar';
            $bbmKendaraan->save();
            DB::commit();
            return response()->json(['message' => 'Foto KM awal berhasil diunggah.', 'data' => $bbmKendaraan], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah foto.', 'error' => $e->getMessage()], 500);
        }
    }

    public function finishFilling(BbmKendaraan $bbmKendaraan)
    {
        $bbmKendaraan->update(['status_pengisian' => 'selesai isi bbm']);
        return response()->json(['message' => 'Status pengisian berhasil diperbarui.', 'data' => $bbmKendaraan]);
    }

    public function uploadEndKmAndNota(Request $request, BbmKendaraan $bbmKendaraan)
    {
        $validator = Validator::make($request->all(), [
            'end_km_photo' => 'sometimes|image|max:5120',
            'nota_pengisian_photo' => 'sometimes|image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $bbmKendaraan->fill($this->getFieldsToReset($bbmKendaraan, ['end_km_photo', 'nota_pengisian_photo']));

            if ($request->hasFile('end_km_photo')) {
                if ($bbmKendaraan->end_km_photo_path) Storage::disk('public')->delete($bbmKendaraan->end_km_photo_path);
                $file = $request->file('end_km_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('bbm_photos/end_km', $fileName, 'public');
                $bbmKendaraan->end_km_photo_path = $path;
                $this->triggerVerificationProcess($bbmKendaraan, 'end_km_photo', 'Foto KM Akhir BBM', Storage::url($path));
            }

            if ($request->hasFile('nota_pengisian_photo')) {
                if ($bbmKendaraan->nota_pengisian_photo_path) Storage::disk('public')->delete($bbmKendaraan->nota_pengisian_photo_path);
                $file = $request->file('nota_pengisian_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('bbm_photos/nota', $fileName, 'public');
                $bbmKendaraan->nota_pengisian_photo_path = $path;
                $this->triggerVerificationProcess($bbmKendaraan, 'nota_pengisian_photo', 'Foto Nota BBM', Storage::url($path));
            }
            $bbmKendaraan->status_bbm_kendaraan = 'verifikasi gambar';
            $bbmKendaraan->save();
            DB::commit();
            return response()->json(['message' => 'Foto KM akhir dan nota berhasil diunggah.', 'data' => $bbmKendaraan], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah foto.', 'error' => $e->getMessage()], 500);
        }
    }
}
