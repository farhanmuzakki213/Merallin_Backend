<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRejectedPhotoRequest;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Jobs\EscalateVerificationJob;
use App\Models\User;
use App\Notifications\PhotoVerificationRequired;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\PushSubscription;

class TripController extends Controller
{
    /**
     * Memulai alur notifikasi verifikasi: instan ke admin, dan tertunda ke manajer/direksi.
     */
    private function triggerVerificationProcess(Trip $trip, string $photoType, string $photoDisplayName, string $publicPhotoUrl)
    {
        // dd($trip->user->pushSubscriptions);
        if (!$trip->user) {
            Log::error("Percobaan mengirim notifikasi untuk trip tanpa driver. Trip ID: {$trip->id}");
            return;
        }
        try {
            $admins = User::role('admin')->whereHas('pushSubscriptions')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PhotoVerificationRequired(
                    $trip,
                    $photoDisplayName,
                    $trip->project_name,
                    $publicPhotoUrl
                ));
            }
            $statusField = $photoType . '_status';

            EscalateVerificationJob::dispatch(
                $trip,
                $photoDisplayName,
                $trip->project_name,
                $publicPhotoUrl,
                'manager',
                $statusField
            )->delay(now()->addMinutes(1));

            EscalateVerificationJob::dispatch(
                $trip,
                $photoDisplayName,
                $trip->project_name,
                $publicPhotoUrl,
                'direksi',
                $statusField
            )->delay(now()->addMinutes(2));
        } catch (\Exception $e) {
            Log::error('Gagal memicu proses verifikasi notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Memeriksa foto yang diunggah, jika ada yang sebelumnya 'rejected',
     * maka siapkan data untuk mereset status trip dan status foto tersebut.
     *
     * @param \App\Models\Trip $trip
     * @param array $photoTypes
     * @return array
     */
    private function getFieldsToReset(Trip $trip, array $photoTypes): array
    {
        // Peta jenis foto ke kolom statusnya di database
        $statusMap = [
            'start_km_photo'            => 'start_km_photo_status',
            'muat_photo'                => 'muat_photo_status',
            'bongkar_photo'             => 'bongkar_photo_status',
            'end_km_photo'              => 'end_km_photo_status',
            'delivery_order'            => 'delivery_order_status',
            'timbangan_kendaraan_photo' => 'timbangan_kendaraan_photo_status',
            'segel_photo'               => 'segel_photo_status',
            'delivery_letters_initial'  => 'delivery_letter_initial_status',
            'delivery_letters_final'    => 'delivery_letter_final_status',
        ];

        $updates = [];
        $tripStatusShouldBeReset = false;

        foreach ($photoTypes as $type) {
            $statusField = $statusMap[$type] ?? null;
            // Jika status field ada dan nilainya 'rejected'
            if ($statusField && $trip->{$statusField} === 'rejected') {
                // Tandai bahwa status foto ini perlu diubah ke 'pending'
                $updates[$statusField] = 'pending';
                $tripStatusShouldBeReset = true;
            }
        }

        // Jika ada setidaknya satu foto rejected yang diupload ulang,
        // maka status trip utama juga direset.
        if ($tripStatusShouldBeReset) {
            $updates['status_trip'] = 'verifikasi gambar';
        }

        return $updates;
    }

    /**
     * Membuat nama file yang unik berdasarkan nama user, tanggal, dan kode unik.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function generateUniqueFileName($file)
    {
        $userName = Str::slug(Auth::user()->name, '-');

        $timestamp = now()->format('Ymd_His');

        $uniqueId = uniqid();

        $extension = $file->getClientOriginalExtension();

        return "{$userName}_{$timestamp}_{$uniqueId}.{$extension}";
    }

    /**
     * Driver mengambil/menerima trip yang dibuat oleh Admin.
     */
    public function acceptTrip(Trip $trip)
    {
        $trip->update([
            'user_id'     => Auth::id(),
            'status_trip' => 'proses',
        ]);

        return response()->json(['message' => 'Anda berhasil mengambil trip.', 'data' => $trip]);
    }

    /**
     * Driver mengupload data awal perjalanan.
     */
    public function updateStart(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'license_plate'   => 'sometimes|string|max:20',
            'start_km'        => 'sometimes|integer',
            'start_km_photo'  => 'sometimes|image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();
            $trip->fill($this->getFieldsToReset($trip, ['start_km_photo']));

            if (isset($validated['license_plate'])) $trip->license_plate = $validated['license_plate'];
            if (isset($validated['start_km'])) $trip->start_km = $validated['start_km'];

            if ($request->hasFile('start_km_photo')) {
                if ($trip->start_km_photo_path) Storage::disk('public')->delete($trip->start_km_photo_path);

                $file = $request->file('start_km_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/start_km_photo', $fileName, 'public');

                $trip->start_km_photo_path = $path;
                $trip->start_km_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();

                $this->triggerVerificationProcess($trip, 'start_km_photo', 'Foto KM Awal', Storage::url($path));
            } else {
                $trip->save();
            }

            DB::commit();
            return response()->json(['message' => 'Data awal perjalanan berhasil diperbarui.', 'data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui data awal perjalanan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Driver update status saat tiba di lokasi muat.
     */
    public function updateAtLoadingPoint(Trip $trip)
    {
        $trip->update([
            'status_lokasi' => 'di lokasi muat',
            'status_muatan' => 'proses muat',
        ]);
        return response()->json(['message' => 'Status berhasil diupdate: Tiba di lokasi muat.', 'data' => $trip]);
    }

    /**
     * Driver konfirmasi telah selesai melakukan proses muat.
     */
    public function finishLoading(Trip $trip)
    {
        $trip->update([
            'status_muatan' => 'selesai muat',
        ]);
        return response()->json(['message' => 'Status berhasil diupdate: Proses muat selesai.', 'data' => $trip]);
    }

    /**
     * Driver mengupload data setelah selesai muat.
     */
    public function updateAfterLoading(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'muat_photo'        => 'sometimes|image|max:5120',
            'delivery_letters'    => 'sometimes|array',
            'delivery_letters.*'  => 'image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $trip->fill($this->getFieldsToReset($trip, ['muat_photo', 'delivery_letters_initial']));

            if ($request->hasFile('muat_photo')) {
                if ($trip->muat_photo_path) Storage::disk('public')->delete($trip->muat_photo_path);

                $file = $request->file('muat_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/muat_photo', $fileName, 'public');

                $trip->muat_photo_path = $path;
                $trip->muat_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();

                $this->triggerVerificationProcess($trip, 'muat_photo', 'Foto Muat', Storage::url($path));
            }

            if ($request->hasFile('delivery_letters')) {
                $deliveryData = $trip->delivery_letter_path ?? ['initial_letters' => [], 'final_letters' => []];
                if (!empty($deliveryData['initial_letters'])) {
                    Storage::disk('public')->delete($deliveryData['initial_letters']);
                }

                $initialPaths = [];
                foreach ($request->file('delivery_letters') as $file) {
                    $fileName = $this->generateUniqueFileName($file);
                    $initialPaths[] = $file->storeAs('trip_photos/delivery_letters', $fileName, 'public');
                }
                $deliveryData['initial_letters'] = $initialPaths;
                $trip->delivery_letter_path = $deliveryData;
                $trip->delivery_letter_initial_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();

                $this->triggerVerificationProcess($trip, 'delivery_letters_initial', 'Surat Jalan Awal', Storage::url($initialPaths[0]));
            }

            DB::commit();
            return response()->json(['message' => 'Dokumen setelah muat berhasil diunggah.', 'data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah dokumen setelah muat.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Driver mengupload dokumen tambahan (DO, Timbangan, Segel).
     */
    public function uploadTripDocuments(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'delivery_order' => 'sometimes|image|max:5120',
            'segel_photo' => 'sometimes|image|max:5120',
            'timbangan_kendaraan_photo' => 'sometimes|image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $trip->fill($this->getFieldsToReset($trip, ['delivery_order', 'segel_photo', 'timbangan_kendaraan_photo']));

            if ($request->hasFile('delivery_order')) {
                if ($trip->delivery_order_path) Storage::disk('public')->delete($trip->delivery_order_path);
                $file = $request->file('delivery_order');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/delivery_order', $fileName, 'public');
                $trip->delivery_order_path = $path;
                $trip->delivery_order_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'delivery_order', 'Delivery Order', Storage::url($path));
            }

            if ($request->hasFile('segel_photo')) {
                if ($trip->segel_photo_path) Storage::disk('public')->delete($trip->segel_photo_path);
                $file = $request->file('segel_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/segel_photo', $fileName, 'public');
                $trip->segel_photo_path = $path;
                $trip->segel_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'segel_photo', 'Foto Segel', Storage::url($path));
            }

            if ($request->hasFile('timbangan_kendaraan_photo')) {
                if ($trip->timbangan_kendaraan_photo_path) Storage::disk('public')->delete($trip->timbangan_kendaraan_photo_path);
                $file = $request->file('timbangan_kendaraan_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/timbangan_kendaraan', $fileName, 'public');
                $trip->timbangan_kendaraan_photo_path = $path;
                $trip->timbangan_kendaraan_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'timbangan_kendaraan_photo', 'Foto Timbangan', Storage::url($path));
            }

            DB::commit();
            return response()->json(['message' => 'Dokumen tambahan berhasil diunggah.', 'data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah dokumen tambahan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Driver update status saat tiba di lokasi bongkar.
     */
    public function updateAtUnloadingPoint(Trip $trip)
    {
        DB::beginTransaction();
        try {
            $trip->update([
                'status_lokasi' => 'di lokasi bongkar',
                'status_muatan' => 'proses bongkar',
            ]);

            $normalizedLicensePlate = Str::lower(str_replace(' ', '', $trip->license_plate));

            $vehicle = Vehicle::whereRaw("LOWER(REPLACE(license_plate, ' ', '')) = ?", [$normalizedLicensePlate])->first();

            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'license_plate' => $normalizedLicensePlate,
                ]);
            }

            VehicleLocation::create([
                'vehicle_id'  => $vehicle->id,
                'user_id'     => $trip->user_id,
                'location'    => $trip->destination,
                'event_type'  => 'trip_completion',
                'trip_id'     => $trip->id,
                'remarks'     => 'Telah tiba di lokasi bongkar: ' . $trip->destination,
                'reported_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Status berhasil diupdate dan lokasi kendaraan telah dicatat.',
                'data' => $trip,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Status Gagal diupdate.', 'error' => $th->getMessage()], 422);
        }
    }

    /**
     * Driver konfirmasi telah selesai melakukan proses bongkar.
     */
    public function finishUnloading(Trip $trip)
    {
        $trip->update([
            'status_muatan' => 'selesai bongkar',
        ]);
        return response()->json(['message' => 'Status berhasil diupdate: Proses bongkar selesai.', 'data' => $trip]);
    }

    /**
     * Driver mengupload data akhir setelah selesai bongkar.
     */
    public function updateFinish(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'end_km'            => 'sometimes|integer|gt:' . ($trip->start_km ?? 0),
            'end_km_photo'      => 'sometimes|image|max:5120',
            'bongkar_photo'     => 'sometimes|array',
            'bongkar_photo.*'   => 'image|max:5120',
            'delivery_letters'  => 'sometimes|array',
            'delivery_letters.*'=> 'image|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();
            $trip->fill($this->getFieldsToReset($trip, ['end_km_photo', 'bongkar_photo', 'delivery_letters_final']));

            if (isset($validated['end_km'])) $trip->end_km = $validated['end_km'];

            if ($request->hasFile('end_km_photo')) {
                if ($trip->end_km_photo_path) Storage::disk('public')->delete($trip->end_km_photo_path);
                $file = $request->file('end_km_photo');
                $fileName = $this->generateUniqueFileName($file);
                $path = $file->storeAs('trip_photos/end_km_photo', $fileName, 'public');
                $trip->end_km_photo_path = $path;
                $trip->end_km_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'end_km_photo', 'Foto KM Akhir', Storage::url($path));
            }

            if ($request->hasFile('bongkar_photo')) {
                if ($trip->bongkar_photo_path) {
                    Storage::disk('public')->delete($trip->bongkar_photo_path);
                }
                $bongkarPaths = [];
                foreach ($request->file('bongkar_photo') as $file) {
                    $fileName = $this->generateUniqueFileName($file);
                    $bongkarPaths[] = $file->storeAs('trip_photos/bongkar_photo', $fileName, 'public');
                }
                $trip->bongkar_photo_path = $bongkarPaths;
                $trip->bongkar_photo_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'bongkar_photo', 'Foto Bongkar', Storage::url($bongkarPaths[0]));
            }

            if ($request->hasFile('delivery_letters')) {
                $deliveryData = $trip->delivery_letter_path ?? ['initial_letters' => [], 'final_letters' => []];
                if (!empty($deliveryData['final_letters'])) {
                    Storage::disk('public')->delete($deliveryData['final_letters']);
                }
                $finalPaths = [];
                foreach ($request->file('delivery_letters') as $file) {
                    $fileName = $this->generateUniqueFileName($file);
                    $finalPaths[] = $file->storeAs('trip_photos/delivery_letters', $fileName, 'public');
                }
                $deliveryData['final_letters'] = $finalPaths;
                $trip->delivery_letter_path = $deliveryData;
                $trip->delivery_letter_final_status = 'pending';
                $trip->status_trip = 'verifikasi gambar';
                $trip->save();
                $this->triggerVerificationProcess($trip, 'delivery_letters_final', 'Surat Jalan Akhir', Storage::url($finalPaths[0]));
            }

            $trip->save();
            DB::commit();
            return response()->json(['message' => 'Data akhir perjalanan berhasil diperbarui.', 'data' => $trip], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui data akhir perjalanan.', 'error' => $e->getMessage()], 500);
        }
    }

    // =================================================================
    // FUNGSI UNTUK MELIHAT DATA (GET)
    // =================================================================

    /**
     * Melihat perjalanan yang tersedia atau yang sedang dijalani oleh driver.
     */
    public function indexDriver()
    {
        $driverId = Auth::id();
        $trips = Trip::with('user')
            ->where('status_trip', 'tersedia')
            ->orWhere(function ($query) use ($driverId) {
                $query->where('user_id', $driverId);
            })
            ->latest()
            ->get();

        return response()->json($trips);
    }

    /**
     * Melihat detail satu perjalanan.
     */
    public function show(Trip $trip)
    {
        return response()->json($trip->load('user'));
    }
}
