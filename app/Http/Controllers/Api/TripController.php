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

    // =================================================================
    // FUNGSI UNTUK DRIVER
    // =================================================================

    /**
     * Driver membuat trip sendiri.
     */
    public function storeByDriver(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'origin'       => 'required|string|max:255',
            'destination'  => 'required|string|max:255',
        ]);

        $trip = Trip::create([
            'user_id'      => Auth::id(),
            'project_name' => $validated['project_name'],
            'origin'       => $validated['origin'],
            'destination'  => $validated['destination'],
            'status_trip'  => 'proses',
        ]);

        return response()->json(['message' => 'Trip berhasil dibuat.', 'data' => $trip], 201);
    }

    public function updateByDriver(Request $request, Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mengedit trip ini.'], 403);
        }
        if ($trip->status_trip !== 'proses' || $trip->start_km !== null) {
            return response()->json(['message' => 'Trip tidak dapat diedit karena perjalanan sudah dimulai.'], 422);
        }

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'origin'       => 'required|string|max:255',
            'destination'  => 'required|string|max:255',
        ]);

        $trip->update($validated);

        return response()->json(['message' => 'Trip berhasil diperbarui.', 'data' => $trip]);
    }

    /**
     * [BARU] Driver menghapus trip yang ia buat.
     */
    public function destroyByDriver(Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus trip ini.'], 403);
        }
        if ($trip->status_trip !== 'proses' || $trip->start_km !== null) {
            return response()->json(['message' => 'Trip tidak dapat dihapus karena perjalanan sudah dimulai.'], 422);
        }
        $trip->delete();

        return response()->json(['message' => 'Trip berhasil dihapus.']);
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
            'bongkar_photo'     => 'required|image|max:5120',
            'end_km_photo'      => 'required|image|max:5120',
            'end_km'            => 'required|integer|gte:' . $startKmValue,
            'delivery_letters'    => 'required|array',
            'delivery_letters.*'  => 'required|file|mimes:jpg,png|max:5120',
        ]);

        $bongkarFile = $request->file('bongkar_photo');
        $bongkarFileName = $this->generateUniqueFileName($bongkarFile);
        $bongkarPath = $bongkarFile->storeAs('trip_photos/bongkar_photo', $bongkarFileName, 'public');

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
            'bongkar_photo_path' => $bongkarPath,
            'end_km_photo_path'  => $endKmPath,
            'end_km'             => $validated['end_km'],
            'delivery_letter_path' => $deliveryData,
            'status_trip'        => 'selesai',
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
