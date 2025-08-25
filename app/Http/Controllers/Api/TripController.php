<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Pastikan trip tersedia dan belum diambil
        if ($trip->status_trip !== 'tersedia' || $trip->user_id !== null) {
            return response()->json(['message' => 'Trip ini tidak tersedia atau sudah diambil.'], 422);
        }

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
