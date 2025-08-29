<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\EscalateVehicleLocationVerificationJob;
use App\Models\User;
use App\Models\VehicleLocation;
use App\Notifications\VehicleLocationPhotoVerificationRequired;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VehicleLocationController extends Controller
{
    private function triggerVerificationProcess(VehicleLocation $location, string $photoType, string $photoDisplayName, string $publicPhotoUrl)
    {
        try {
            $admins = User::role('admin')->whereHas('pushSubscriptions')->get();
            $vehicleInfo = $location->vehicle->license_plate ?? 'N/A';

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new VehicleLocationPhotoVerificationRequired($location, $photoDisplayName, $vehicleInfo, $publicPhotoUrl));
            }

            $statusField = $photoType . '_status';
            EscalateVehicleLocationVerificationJob::dispatch($location, $photoDisplayName, $vehicleInfo, $publicPhotoUrl, 'manager', $statusField)->delay(now()->addMinutes(1));
            EscalateVehicleLocationVerificationJob::dispatch($location, $photoDisplayName, $vehicleInfo, $publicPhotoUrl, 'direksi', $statusField)->delay(now()->addMinutes(2));
        } catch (\Exception $e) {
            Log::error('Gagal memicu proses verifikasi Lokasi Kendaraan: ' . $e->getMessage());
        }
    }

    private function getFieldsToReset(VehicleLocation $location, array $photoTypes): array
    {
        $statusMap = [
            'standby_photo' => 'standby_photo_status',
            'start_km_photo' => 'start_km_photo_status',
            'end_km_photo' => 'end_km_photo_status',
        ];
        $updates = [];
        $shouldResetOverallStatus = false;

        foreach ($photoTypes as $type) {
            $statusField = $statusMap[$type] ?? null;
            if ($statusField && $location->{$statusField} === 'rejected') {
                $updates[$statusField] = 'pending';
                $shouldResetOverallStatus = true;
            }
        }
        if ($shouldResetOverallStatus) {
            $updates['status_vehicle_location'] = 'verifikasi gambar';
        }
        return $updates;
    }

    private function generateUniqueFileName($file): string
    {
        return Str::slug(Auth::user()->name) . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    }

    public function index()
    {
        $history = VehicleLocation::with('vehicle')->where('user_id', Auth::id())->latest()->get();
        return response()->json($history);
    }

    public function show(VehicleLocation $vehicleLocation)
    {
        if ($vehicleLocation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($vehicleLocation->load('vehicle', 'user'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'keterangan' => 'required|string|max:255',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $location = VehicleLocation::create([
            'user_id' => Auth::id(),
            'vehicle_id' => $request->vehicle_id,
            'keterangan' => $request->keterangan,
            'status_lokasi' => 'stanby',
        ]);

        return response()->json(['message' => 'Data lokasi berhasil dibuat.', 'data' => $location], 201);
    }

    public function uploadStandbyAndStartKm(Request $request, VehicleLocation $vehicleLocation)
    {
        $validator = Validator::make($request->all(), [
            'standby_photo' => 'sometimes|image|max:5120',
            'start_km_photo' => 'sometimes|image|max:5120',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        DB::beginTransaction();
        try {
            $vehicleLocation->fill($this->getFieldsToReset($vehicleLocation, ['standby_photo', 'start_km_photo']));

            if ($request->hasFile('standby_photo')) {
                if ($vehicleLocation->standby_photo_path) Storage::disk('public')->delete($vehicleLocation->standby_photo_path);
                $path = $request->file('standby_photo')->storeAs('location_photos/standby', $this->generateUniqueFileName($request->file('standby_photo')), 'public');
                $vehicleLocation->standby_photo_path = $path;
                $this->triggerVerificationProcess($vehicleLocation, 'standby_photo', 'Foto Stanby', Storage::url($path));
            }
            if ($request->hasFile('start_km_photo')) {
                if ($vehicleLocation->start_km_photo_path) Storage::disk('public')->delete($vehicleLocation->start_km_photo_path);
                $path = $request->file('start_km_photo')->storeAs('location_photos/start_km', $this->generateUniqueFileName($request->file('start_km_photo')), 'public');
                $vehicleLocation->start_km_photo_path = $path;
                $this->triggerVerificationProcess($vehicleLocation, 'start_km_photo', 'Foto KM Awal', Storage::url($path));
            }

            $vehicleLocation->start_location = ['latitude' => $request->latitude, 'longitude' => $request->longitude];
            $vehicleLocation->status_lokasi = 'menuju lokasi';
            $vehicleLocation->save();

            DB::commit();
            return response()->json(['message' => 'Foto stanby dan KM awal berhasil diunggah.', 'data' => $vehicleLocation]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah foto.', 'error' => $e->getMessage()], 500);
        }
    }

    public function arriveAtLocation(VehicleLocation $vehicleLocation)
    {
        $vehicleLocation->update(['status_lokasi' => 'sampai di lokasi']);
        return response()->json(['message' => 'Status lokasi berhasil diperbarui.', 'data' => $vehicleLocation]);
    }

    public function uploadEndKm(Request $request, VehicleLocation $vehicleLocation)
    {
        $validator = Validator::make($request->all(), [
            'end_km_photo' => 'required|image|max:5120',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        DB::beginTransaction();
        try {
            $vehicleLocation->fill($this->getFieldsToReset($vehicleLocation, ['end_km_photo']));

            if ($request->hasFile('end_km_photo')) {
                if ($vehicleLocation->end_km_photo_path) Storage::disk('public')->delete($vehicleLocation->end_km_photo_path);
                $path = $request->file('end_km_photo')->storeAs('location_photos/end_km', $this->generateUniqueFileName($request->file('end_km_photo')), 'public');
                $vehicleLocation->end_km_photo_path = $path;
                $this->triggerVerificationProcess($vehicleLocation, 'end_km_photo', 'Foto KM Akhir', Storage::url($path));
            }

            $vehicleLocation->end_location = ['latitude' => $request->latitude, 'longitude' => $request->longitude];
            $vehicleLocation->status_lokasi = null;
            $vehicleLocation->status_vehicle_location = 'verifikasi gambar';
            $vehicleLocation->save();

            DB::commit();
            return response()->json(['message' => 'Foto KM akhir berhasil diunggah.', 'data' => $vehicleLocation]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah foto.', 'error' => $e->getMessage()], 500);
        }
    }
}
