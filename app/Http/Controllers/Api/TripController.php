<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRejectedPhotoRequest;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TripController extends Controller
{
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
        $validated = $request->validate([
            'license_plate'   => 'required|string|max:20',
            'start_km'        => 'required|integer',
            'start_km_photo'  => 'required|image|max:5120',
        ]);

        $photoFile = $request->file('start_km_photo');
        $fileName = $this->generateUniqueFileName($photoFile);
        $path = $photoFile->storeAs('trip_photos/start_km_photo', $fileName, 'public');

        $trip->update([
            'license_plate'       => $validated['license_plate'],
            'start_km'            => $validated['start_km'],
            'start_km_photo_path' => $path,
            'status_lokasi'       => 'menuju lokasi muat',
            'status_muatan'       => 'kosong',
        ]);

        return response()->json(['message' => 'Data awal perjalanan berhasil diupdate.', 'data' => $trip]);
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
        $validated = $request->validate([
            'muat_photo'        => 'required|image|max:5120',
            'delivery_letters'    => 'required|array',
            'delivery_letters.*'  => 'required|file|mimes:jpg,png|max:5120',
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

        $trip->update([
            'muat_photo_path'      => $muatPath,
            'delivery_letter_path' => $deliveryData,
            'status_lokasi'        => 'menuju lokasi bongkar',
            'status_muatan'        => 'termuat',
        ]);

        return response()->json(['message' => 'Data muatan berhasil diupload.', 'data' => $trip]);
    }

    /**
     * Driver mengupload dokumen tambahan (DO, Timbangan, Segel).
     */
    public function uploadTripDocuments(Request $request, Trip $trip)
    {
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

        $trip->update($updateData);

        return response()->json(['message' => 'Dokumen tambahan berhasil diupload.', 'data' => $trip]);
    }

    /**
     * Driver update status saat tiba di lokasi bongkar.
     */
    public function updateAtUnloadingPoint(Trip $trip)
    {
        $trip->update([
            'status_lokasi' => 'di lokasi bongkar',
            'status_muatan' => 'proses bongkar',
        ]);
        return response()->json(['message' => 'Status berhasil diupdate: Tiba di lokasi bongkar.', 'data' => $trip]);
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
        $startKmValue = $trip->start_km;
        $validated = $request->validate([
            'bongkar_photo'    => 'required|array',
            'bongkar_photo.*'  => 'required|file|mimes:jpg,png|max:5120',
            'end_km_photo'      => 'required|image|max:5120',
            'end_km'            => 'required|integer|gte:' . $startKmValue,
            'delivery_letters'    => 'required|array',
            'delivery_letters.*'  => 'required|file|mimes:jpg,png|max:5120',
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

        $trip->update([
            'bongkar_photo_path' => $bongkarPaths,
            'end_km_photo_path'  => $endKmPath,
            'end_km'             => $validated['end_km'],
            'delivery_letter_path' => $deliveryData,
            'status_lokasi'      => null,
            'status_muatan'      => null,
        ]);

        return response()->json(['message' => 'Perjalanan telah selesai.', 'data' => $trip]);
    }

    /**
     * [REFACTORED V2] Endpoint fleksibel untuk driver mengupdate foto yang ditolak oleh admin.
     * Kini mendukung pembaruan 'delivery_letters' secara spesifik (initial/final).
     */
    public function updateRejectedPhoto(UpdateRejectedPhotoRequest $request, Trip $trip): JsonResponse
    {
        $validated = $request->validated();
        $photoType = $validated['photo_type'];
        $photoFile = $validated['photo_file'];

        // 1. Dapatkan konfigurasi untuk tipe foto yang diberikan
        $config = $this->getPhotoTypeConfig($photoType);
        if (!$config) {
            return response()->json(['message' => 'Tipe foto tidak valid.'], 422);
        }

        // 2. Verifikasi bahwa status foto saat ini adalah 'rejected'
        $statusColumn = $config['status_column'];
        if ($trip->{$statusColumn} !== 'rejected') {
            return response()->json(['message' => 'Hanya foto dengan status "rejected" yang bisa diupdate.'], 403);
        }

        // 3. Simpan foto baru dan dapatkan path-nya
        $newPath = $photoFile->storeAs($config['folder'], $this->generateUniqueFileName($photoFile), 'public');

        // 4. Proses pembaruan path dan hapus foto lama
        $updateData = [];
        $pathColumn = $config['path_column'];
        $oldPathToDelete = null;

        if (!$config['is_multiple']) {
            // Untuk foto tunggal
            $oldPathToDelete = $trip->{$pathColumn};
            $updateData[$pathColumn] = $newPath;
        } else {
            // Untuk foto ganda (array)
            $oldPhotoPath = $validated['old_photo_path'];
            $currentPaths = $trip->{$pathColumn} ?? [];
            // [MODIFIKASI] Ambil 'letter_type' dari request yang sudah divalidasi
            $letterType = $validated['letter_type'] ?? null;

            // [MODIFIKASI] Kirim 'letterType' ke metode helper
            list($updatedPaths, $pathFound) = $this->updatePathInArray($currentPaths, $oldPhotoPath, $newPath, $photoType, $letterType);

            if (!$pathFound) {
                Storage::disk('public')->delete($newPath); // Hapus file baru jika path lama tidak ditemukan
                return response()->json(['message' => 'Path foto lama yang spesifik tidak ditemukan.'], 404);
            }
            $oldPathToDelete = $oldPhotoPath;
            $updateData[$pathColumn] = $updatedPaths;
        }

        // Hapus file foto lama dari storage jika ada
        if ($oldPathToDelete) {
            Storage::disk('public')->delete($oldPathToDelete);
        }

        // 5. Siapkan data untuk mereset status verifikasi di database
        $baseColumn = $config['base_column'];
        $updateData[$statusColumn] = 'pending';
        $updateData[$baseColumn . '_verified_by'] = null;
        $updateData[$baseColumn . '_verified_at'] = null;
        $updateData[$baseColumn . '_rejection_reason'] = null;

        // 6. Update data trip
        $trip->update($updateData);

        // 7. Kembalikan respons sukses dengan data terbaru
        return response()->json([
            'message' => 'Foto berhasil diperbarui dan menunggu verifikasi ulang.',
            'data' => $trip->fresh()
        ]);
    }

    /**
     * [REFACTORED V2] Memperbarui path foto di dalam array untuk tipe foto ganda.
     *
     * @param array $currentPaths
     * @param string $oldPath
     * @param string $newPath
     * @param string $photoType
     * @param string|null $letterType // [MODIFIKASI] Parameter baru
     * @return array [array $updatedPaths, bool $pathFound]
     */
    private function updatePathInArray(array $currentPaths, string $oldPath, string $newPath, string $photoType, ?string $letterType = null): array
    {
        $pathFound = false;

        if ($photoType === 'bongkar_photo') {
            $key = array_search($oldPath, $currentPaths);
            if ($key !== false) {
                $currentPaths[$key] = $newPath;
                $pathFound = true;
            }
        } elseif ($photoType === 'delivery_letters') {
            // [LOGIKA BARU] Langsung menargetkan array yang benar berdasarkan letterType
            if (!$letterType) {
                // Seharusnya tidak terjadi karena sudah divalidasi, tapi ini sebagai pengaman
                return [$currentPaths, false];
            }

            $targetKey = ($letterType === 'initial') ? 'initial_letters' : 'final_letters';

            if (!empty($currentPaths[$targetKey]) && is_array($currentPaths[$targetKey])) {
                $index = array_search($oldPath, $currentPaths[$targetKey]);
                if ($index !== false) {
                    $currentPaths[$targetKey][$index] = $newPath;
                    $pathFound = true;
                }
            }
        }

        return [$currentPaths, $pathFound];
    }

    /**
     * Mendapatkan konfigurasi (nama kolom, folder, dll) untuk setiap tipe foto.
     *
     * @param string $photoType
     * @return array|null
     */
    private function getPhotoTypeConfig(string $photoType): ?array
    {
        $config = [
            'start_km_photo' => [
                'path_column' => 'start_km_photo_path',
                'status_column' => 'start_km_photo_status',
                'base_column' => 'start_km_photo',
                'folder' => 'trip_photos/start_km_photo',
                'is_multiple' => false,
            ],
            'muat_photo' => [
                'path_column' => 'muat_photo_path',
                'status_column' => 'muat_photo_status',
                'base_column' => 'muat_photo',
                'folder' => 'trip_photos/muat_photo',
                'is_multiple' => false,
            ],
            'delivery_order' => [
                'path_column' => 'delivery_order_path',
                'status_column' => 'delivery_order_status',
                'base_column' => 'delivery_order',
                'folder' => 'trip_photos/delivery_order',
                'is_multiple' => false,
            ],
            'timbangan_kendaraan_photo' => [
                'path_column' => 'timbangan_kendaraan_photo_path',
                'status_column' => 'timbangan_kendaraan_photo_status',
                'base_column' => 'timbangan_kendaraan_photo',
                'folder' => 'trip_photos/timbangan_kendaraan',
                'is_multiple' => false,
            ],
            'segel_photo' => [
                'path_column' => 'segel_photo_path',
                'status_column' => 'segel_photo_status',
                'base_column' => 'segel_photo',
                'folder' => 'trip_photos/segel_photo',
                'is_multiple' => false,
            ],
            'end_km_photo' => [
                'path_column' => 'end_km_photo_path',
                'status_column' => 'end_km_photo_status',
                'base_column' => 'end_km_photo',
                'folder' => 'trip_photos/end_km_photo',
                'is_multiple' => false,
            ],
            'bongkar_photo' => [
                'path_column' => 'bongkar_photo_path',
                'status_column' => 'bongkar_photo_status',
                'base_column' => 'bongkar_photo',
                'folder' => 'trip_photos/bongkar_photo',
                'is_multiple' => true,
            ],
            'delivery_letters' => [
                'path_column' => 'delivery_letter_path',
                'status_column' => 'delivery_letter_status',
                'base_column' => 'delivery_letter',
                'folder' => 'trip_photos/delivery_letters',
                'is_multiple' => true,
            ],
        ];

        return $config[$photoType] ?? null;
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
