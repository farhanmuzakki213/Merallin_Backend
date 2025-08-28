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
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\PushSubscription;

class TripController extends Controller
{
    /**
     * Memulai alur notifikasi verifikasi: instan ke admin, dan tertunda ke manajer/direksi.
     */
    private function triggerVerificationProcess(Trip $trip, string $photoType, string $photoName)
    {
        // dd($trip->user->pushSubscriptions);
        if (!$trip->user) {
            Log::error("Percobaan mengirim notifikasi untuk trip tanpa driver. Trip ID: {$trip->id}");
            return;
        }
        try {
            $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->whereHas('pushSubscriptions')
                ->get();

            $message = "Foto '{$photoName}' dari driver {$trip->user->name} perlu diverifikasi.";
            foreach ($admins as $admin) {
                // notifyNow mengirim langsung, tidak melalui antrian. Ini pilihan yang baik.
                $admin->notifyNow(new PhotoVerificationRequired($trip, $message));
            }

            // Menjadwalkan Job Eskalasi
            EscalateVerificationJob::dispatch($trip, $photoType, 'manager')->delay(now()->addMinutes(1));
            EscalateVerificationJob::dispatch($trip, $photoType, 'direksi')->delay(now()->addMinutes(2));
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
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'license_plate'   => 'required|string|max:20',
                'start_km'        => 'required|integer',
                'start_km_photo'  => 'required|image|max:5120',
            ]);

            $photoFile = $request->file('start_km_photo');
            $fileName = $this->generateUniqueFileName($photoFile);
            $path = $photoFile->storeAs('trip_photos/start_km_photo', $fileName, 'public');

            $updateData = [
                'license_plate'       => $request->license_plate,
                'start_km'            => $request->start_km,
                'start_km_photo_path' => $path,
                'status_lokasi'       => 'menuju lokasi muat',
                'status_muatan'       => 'kosong',
            ];

            // Cek dan dapatkan field yang perlu direset, lalu gabungkan
            $resets = $this->getFieldsToReset($trip, ['start_km_photo']);
            $updateData = array_merge($updateData, $resets);
            $trip->update($updateData);

            $trip->refresh();
            $this->triggerVerificationProcess($trip, 'start_km_photo', 'Foto KM Awal');

            DB::commit();
            return response()->json(['message' => 'Data awal perjalanan berhasil diupdate.', 'data' => $trip]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Data awal perjalanan Gagal diupdate.', 'error' => $e->getMessage()], 422);
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
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'muat_photo'        => 'required|image|max:5120',
                'delivery_letters'    => 'required|array',
                'delivery_letters.*'  => 'required|file|mimes:jpg,png|max:10240',
            ]);

            $muatPhotoFile = $request->file('muat_photo');
            $muatPhotoName = $this->generateUniqueFileName($muatPhotoFile);
            $muatPath = $muatPhotoFile->storeAs('trip_photos/muat_photo', $muatPhotoName, 'public');
            $initialLetterPaths = [];
            if ($request->hasFile('delivery_letters')) {
                foreach ($request->file('delivery_letters') as $file) {
                    $letterName = $this->generateUniqueFileName($file);
                    $initialLetterPaths[] = $file->storeAs('trip_photos/delivery_letters', $letterName, 'public');
                }
            }
            $deliveryData = ['initial_letters' => $initialLetterPaths];

            $updateData = [
                'muat_photo_path'      => $muatPath,
                'delivery_letter_path' => $deliveryData,
                'status_lokasi'        => 'menuju lokasi bongkar',
                'status_muatan'        => 'termuat',
            ];

            // Cek dan dapatkan field yang perlu direset, lalu gabungkan
            $resets = $this->getFieldsToReset($trip, ['muat_photo', 'delivery_letters_initial']);
            $updateData = array_merge($updateData, $resets);
            $trip->update($updateData);

            $trip->refresh();
            $this->triggerVerificationProcess($trip, 'muat_photo', 'Foto Muat');
            $this->triggerVerificationProcess($trip, 'delivery_letter_initial', 'Surat Jalan Awal');

            DB::commit();
            return response()->json(['message' => 'Data muatan berhasil diupload.', 'data' => $trip]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Data muatan Gagal diupload.', 'error' => $th->getMessage()], 422);
        }
    }

    /**
     * Driver mengupload dokumen tambahan (DO, Timbangan, Segel).
     */
    public function uploadTripDocuments(Request $request, Trip $trip)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'delivery_order'            => 'required|image|max:5120',
                'timbangan_kendaraan_photo' => 'required|image|max:5120',
                'segel_photo'               => 'required|image|max:5120',
            ]);

            $updateData = [];

            $doFile = $request->file('delivery_order');
            $doFileName = $this->generateUniqueFileName($doFile);
            $updateData['delivery_order_path'] = $doFile->storeAs('trip_photos/delivery_order', $doFileName, 'public');

            $timbanganFile = $request->file('timbangan_kendaraan_photo');
            $timbanganFileName = $this->generateUniqueFileName($timbanganFile);
            $updateData['timbangan_kendaraan_photo_path'] = $timbanganFile->storeAs('trip_photos/timbangan_kendaraan', $timbanganFileName, 'public');

            $segelFile = $request->file('segel_photo');
            $segelFileName = $this->generateUniqueFileName($segelFile);
            $updateData['segel_photo_path'] = $segelFile->storeAs('trip_photos/segel_photo', $segelFileName, 'public');

            $resets = $this->getFieldsToReset($trip, ['delivery_order', 'timbangan_kendaraan_photo', 'segel_photo']);
            $updateData = array_merge($updateData, $resets);

            $trip->update($updateData);

            $trip->refresh();
            $this->triggerVerificationProcess($trip, 'delivery_order', 'Delivery Order');
            $this->triggerVerificationProcess($trip, 'timbangan_kendaraan_photo', 'Foto Timbangan');
            $this->triggerVerificationProcess($trip, 'segel_photo', 'Foto Segel');

            DB::commit();
            return response()->json(['message' => 'Dokumen tambahan berhasil diupload.', 'data' => $trip]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Dokumen tambahan Gagal diupload.', 'error' => $th->getMessage()], 422);
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
        DB::beginTransaction();
        try {
            $startKmValue = $trip->start_km;
            $validated = $request->validate([
                'bongkar_photo'    => 'required|array',
                'bongkar_photo.*'  => 'required|file|mimes:jpg,png|max:10240',
                'end_km_photo'      => 'required|image|max:5120',
                'end_km'            => 'required|integer|gte:' . $startKmValue,
                'delivery_letters'    => 'required|array',
                'delivery_letters.*'  => 'required|file|mimes:jpg,png|max:10240',
            ]);

            $bongkarPaths = [];
            if ($request->hasFile('bongkar_photo')) {
                foreach ($request->file('bongkar_photo') as $file) {
                    $bongkarFileName = $this->generateUniqueFileName($file);
                    $bongkarPaths[] = $file->storeAs('trip_photos/bongkar_photo', $bongkarFileName, 'public');
                }
            }

            $endKmFile = $request->file('end_km_photo');
            $endKmFileName = $this->generateUniqueFileName($endKmFile);
            $endKmPath = $endKmFile->storeAs('trip_photos/end_km_photo', $endKmFileName, 'public');

            $finalLetterPaths = [];
            if ($request->hasFile('delivery_letters')) {
                foreach ($request->file('delivery_letters') as $file) {
                    $letterName = $this->generateUniqueFileName($file);
                    $finalLetterPaths[] = $file->storeAs('trip_photos/delivery_letters', $letterName, 'public');
                }
            }
            $deliveryData = $trip->delivery_letter_path;
            $deliveryData['final_letters'] = $finalLetterPaths;

            $updateData = [
                'bongkar_photo_path' => $bongkarPaths,
                'end_km_photo_path'  => $endKmPath,
                'end_km'             => $validated['end_km'],
                'delivery_letter_path' => $deliveryData,
                'status_trip'         => 'verifikasi gambar',
                'status_lokasi'      => null,
                'status_muatan'      => null,
            ];

            // Cek dan dapatkan field yang perlu direset, lalu gabungkan
            $resets = $this->getFieldsToReset($trip, ['bongkar_photo', 'end_km_photo', 'delivery_letters_final']);
            $updateData = array_merge($updateData, $resets);

            $trip->update($updateData);

            $trip->refresh();
            $this->triggerVerificationProcess($trip, 'bongkar_photo', 'Foto Bongkar');
            $this->triggerVerificationProcess($trip, 'end_km_photo', 'Foto KM Akhir');
            $this->triggerVerificationProcess($trip, 'delivery_letter_final', 'Surat Jalan Akhir');

            DB::commit();
            return response()->json(['message' => 'Perjalanan telah selesai.', 'data' => $trip]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Perjalanan Gagal selesai.', 'error' => $th->getMessage()], 422);
        }
    }

    /**
     * [REFACTORED V2] Endpoint fleksibel untuk driver mengupdate foto yang ditolak oleh admin.
     * Kini mendukung pembaruan 'delivery_letters' secara spesifik (initial/final).
     */
    public function updateRejectedPhoto(UpdateRejectedPhotoRequest $request, Trip $trip): JsonResponse
    {
        // Peta konfigurasi untuk foto tunggal
        $singlePhotoMap = [
            'start_km_photo'            => ['path' => 'start_km_photo_path', 'status' => 'start_km_photo_status', 'folder' => 'trip_photos/start_km_photo'],
            'muat_photo'                => ['path' => 'muat_photo_path', 'status' => 'muat_photo_status', 'folder' => 'trip_photos/muat_photo'],
            'end_km_photo'              => ['path' => 'end_km_photo_path', 'status' => 'end_km_photo_status', 'folder' => 'trip_photos/end_km_photo'],
            'delivery_order'            => ['path' => 'delivery_order_path', 'status' => 'delivery_order_status', 'folder' => 'trip_photos/delivery_order'],
            'timbangan_kendaraan_photo' => ['path' => 'timbangan_kendaraan_photo_path', 'status' => 'timbangan_kendaraan_photo_status', 'folder' => 'trip_photos/timbangan_kendaraan'],
            'segel_photo'               => ['path' => 'segel_photo_path', 'status' => 'segel_photo_status', 'folder' => 'trip_photos/segel_photo'],
        ];

        // Proses setiap foto tunggal yang diunggah
        foreach ($singlePhotoMap as $field => $config) {
            if ($request->hasFile($field)) {
                if ($trip->{$config['path']}) {
                    Storage::disk('public')->delete($trip->{$config['path']});
                }
                $file = $request->file($field);
                $path = $file->storeAs($config['folder'], $this->generateUniqueFileName($file), 'public');
                $trip->{$config['path']} = $path;
                $trip->{$config['status']} = 'pending';
            }
        }

        // Proses foto array: bongkar_photo
        if ($request->hasFile('bongkar_photo')) {
            if (is_array($trip->bongkar_photo_path)) {
                foreach ($trip->bongkar_photo_path as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $newPaths = [];
            foreach ($request->file('bongkar_photo') as $file) {
                $newPaths[] = $file->storeAs('trip_photos/bongkar_photo', $this->generateUniqueFileName($file), 'public');
            }
            $trip->bongkar_photo_path = $newPaths;
            $trip->bongkar_photo_status = 'pending';
        }

        // Proses foto array: surat jalan (initial & final)
        $deliveryLetterPaths = $trip->delivery_letter_path ?? ['initial_letters' => [], 'final_letters' => []];
        if ($request->hasFile('initial_delivery_letters')) {
            foreach ($deliveryLetterPaths['initial_letters'] as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $newPaths = [];
            foreach ($request->file('initial_delivery_letters') as $file) {
                $newPaths[] = $file->storeAs('trip_photos/delivery_letters', $this->generateUniqueFileName($file), 'public');
            }
            $deliveryLetterPaths['initial_letters'] = $newPaths;
            $trip->delivery_letter_initial_status = 'pending';
        }
        if ($request->hasFile('final_delivery_letters')) {
            foreach ($deliveryLetterPaths['final_letters'] as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $newPaths = [];
            foreach ($request->file('final_delivery_letters') as $file) {
                $newPaths[] = $file->storeAs('trip_photos/delivery_letters', $this->generateUniqueFileName($file), 'public');
            }
            $deliveryLetterPaths['final_letters'] = $newPaths;
            $trip->delivery_letter_final_status = 'pending';
        }
        $trip->delivery_letter_path = $deliveryLetterPaths;

        $trip->status_trip = 'verifikasi gambar';
        $trip->save();

        return response()->json([
            'message' => 'Foto yang ditolak berhasil diperbarui dan menunggu verifikasi ulang.',
            'data' => $trip->fresh()
        ]);
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
