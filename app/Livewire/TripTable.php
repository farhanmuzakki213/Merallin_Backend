<?php

namespace App\Livewire;

use App\Models\Trip;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Exports\TripUserReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleLocation;

#[Layout('layouts.app')]
#[Title('Trip Management')]
class TripTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $detailPerPage = 10;
    public $detailSearch = '';
    public $detailSortField = 'created_at';
    public $detailSortDirection = 'desc';

    public $showModal = false;
    public $tripId;
    public $projectName;
    public $slot_time;
    public $jenis_berat;
    public $origin_address;
    public $origin_link;
    public $destination_address;
    public $destination_link;

    public $showImageModal = false;
    public $imageUrl;

    public $showDeliveryLetterModal = false;
    public $initialLetters = [];
    public $finalLetters = [];
    public $currentInitialIndex = 0;
    public $currentFinalIndex = 0;

    public $userId;
    public $jenisTrip = 'muatan perusahan';
    public $drivers = [];

    public $showRejectionModal = false;
    public $rejectionTripId;
    public $rejectionPhotoType;
    public $rejectionReason = '';

    public $showGalleryModal = false;
    public $galleryTitle = '';
    public $galleryPhotos = [];
    public $currentGalleryIndex = 0;

    /**
     * Mengekstrak koordinat (latitude, longitude) dari URL Google Maps.
     *
     * @param string|null $url
     * @return array|null
     */
    private function getCoordinatesFromGoogleMapsUrl(?string $url): ?array
    {
        if (!$url) {
            return null;
        }

        // Regex untuk mencari pola @latitude,longitude dalam URL
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'latitude' => $matches[1],
                'longitude' => $matches[2],
            ];
        }

        return null;
    }

    /**
     * Fungsi untuk mengambil data dan men-trigger download Excel.
     * Laporan dibuat berdasarkan BULAN DAN TAHUN SAAT INI secara otomatis.
     */
    public function exportReport()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $allTripCounts = Trip::with('user')
            ->where('status_trip', 'selesai')
            ->whereMonth('updated_at', $currentMonth)
            ->whereYear('updated_at', $currentYear)
            ->groupBy('user_id')
            ->selectRaw("
                user_id,
                SUM(CASE WHEN jenis_trip = 'muatan perusahan' THEN 1 ELSE 0 END) as trip_perusahaan,
                SUM(CASE WHEN jenis_trip = 'muatan driver' THEN 1 ELSE 0 END) as trip_driver
            ")
            ->get();

        $dataForExport = [];

        foreach ($allTripCounts as $tripCount) {
            $totalTrip = ($tripCount->trip_perusahaan ?? 0) + ($tripCount->trip_driver ?? 0);

            $dataForExport[] = [
                'driver' => $tripCount->user->name ?? 'User Tidak Ditemukan',
                'bulan' => $now->format('F') . ' ' . $currentYear,
                'trip_perusahaan' => $tripCount->trip_perusahaan ?? 0,
                'trip_driver' => $tripCount->trip_driver ?? 0,
                'total' => $totalTrip,
            ];
        }

        if (empty($dataForExport)) {
            session()->flash('error', 'Tidak ada data trip "selesai" yang ditemukan untuk bulan ini.');
            return;
        }

        $fileName = 'laporan-trip-semua-driver-' . strtolower($now->format('F')) . '-' . $currentYear . '.xlsx';

        return Excel::download(new TripUserReportExport($dataForExport), $fileName);
    }

    public function openGalleryModal($tripId, $photoType)
    {
        $trip = Trip::findOrFail($tripId);
        $photos = [];
        $title = '';

        switch ($photoType) {
            case 'muat':
                $photos = $trip->muat_photo_path ?? [];
                $title = 'Galeri Foto Muat';
                break;
            case 'bongkar':
                $photos = $trip->bongkar_photo_path ?? [];
                $title = 'Galeri Foto Bongkar';
                break;
        }

        $this->galleryPhotos = $photos;
        $this->galleryTitle = $title;
        $this->currentGalleryIndex = 0;
        $this->showGalleryModal = true;
    }

    public function closeGalleryModal()
    {
        $this->showGalleryModal = false;
        $this->galleryTitle = '';
        $this->galleryPhotos = [];
    }

    public function nextGalleryPhoto()
    {
        if ($this->currentGalleryIndex < count($this->galleryPhotos) - 1) {
            $this->currentGalleryIndex++;
        }
    }

    public function previousGalleryPhoto()
    {
        if ($this->currentGalleryIndex > 0) {
            $this->currentGalleryIndex--;
        }
    }

    /**
     * Load data driver saat komponen di-mount.
     */
    public function mount()
    {
        $this->drivers = User::role('driver')->get();
    }

    public function approvePhoto($tripId, $photoType)
    {
        $trip = Trip::findOrFail($tripId);
        $statusField = "{$photoType}_status";
        if ($trip->{$statusField} !== 'pending') {
            session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi oleh admin lain.');
            return;
        }
        $trip->update([
            "{$photoType}_status" => 'approved',
            "{$photoType}_verified_by" => Auth::id(),
            "{$photoType}_verified_at" => now(),
            "{$photoType}_rejection_reason" => null,
        ]);
        $this->updateTripStatus($trip->fresh());

        session()->flash('message', 'Photo has been approved.');
    }

    /**
     * Memperbarui status trip utama berdasarkan status verifikasi semua dokumen.
     */
    private function updateTripStatus(Trip $trip)
    {
        // 1. Definisikan semua jenis foto yang WAJIB ada agar trip dianggap 'selesai'
        $mandatoryPhotos = [
            'start_km_photo', 'km_muat_photo', 'kedatangan_muat_photo',
            'delivery_order_photo', 'muat_photo', 'timbangan_kendaraan_photo',
            'segel_photo', 'end_km_photo', 'kedatangan_bongkar_photo',
            'bongkar_photo', 'delivery_letter_initial', 'delivery_letter_final'
        ];

        $statuses = [];
        $allMandatoryPhotosUploaded = true;

        foreach ($mandatoryPhotos as $type) {
            $pathField = ($type === 'delivery_letter_initial' || $type === 'delivery_letter_final')
                ? 'delivery_letter_path'
                : "{$type}_path";
            $statusField = "{$type}_status";

            $isUploaded = false;
            if ($type === 'delivery_letter_initial') {
                $isUploaded = !empty($trip->delivery_letter_path['initial_letters'] ?? null);
            } elseif ($type === 'delivery_letter_final') {
                $isUploaded = !empty($trip->delivery_letter_path['final_letters'] ?? null);
            } else {
                $isUploaded = !empty($trip->{$pathField});
            }

            if ($isUploaded) {
                $statuses[] = $trip->{$statusField};
            } else {
                // Jika ada foto wajib yang belum diunggah, trip tidak bisa 'selesai'.
                $allMandatoryPhotosUploaded = false;
            }
        }

        // 2. Tentukan status trip baru berdasarkan prioritas
        $newStatus = $trip->status_trip; // Ambil status saat ini sebagai default
        if (in_array('rejected', $statuses)) {
            $newStatus = 'revisi gambar';
        } elseif (!$allMandatoryPhotosUploaded) {
            // Jika dokumen wajib belum lengkap, statusnya pasti 'proses' (kecuali ada yg direject)
            $newStatus = 'proses';
        } elseif (in_array('pending', $statuses)) {
            // Jika sudah lengkap tapi ada yg pending, statusnya 'verifikasi gambar'
            $newStatus = 'verifikasi gambar';
        } else {
            // Jika semua sudah diunggah, lengkap, dan tidak ada yang pending/rejected, maka 'selesai'
            $newStatus = 'selesai';
        }

        // 3. Update status trip jika ada perubahan
        if ($trip->status_trip !== $newStatus) {
            $trip->update(['status_trip' => $newStatus]);
        }

        if ($newStatus === 'selesai' && !VehicleLocation::where('trip_id', $trip->id)->exists()) {
            $originCoords = $this->getCoordinatesFromGoogleMapsUrl(data_get($trip->origin, 'link'));
            $destinationCoords = $this->getCoordinatesFromGoogleMapsUrl(data_get($trip->destination, 'link'));

            VehicleLocation::create([
                'user_id' => $trip->user_id,
                'vehicle_id' => $trip->vehicle_id,
                'trip_id' => $trip->id,
                'keterangan' => "Tugas Trip: {$trip->project_name} (ID: {$trip->id})",
                'start_location' => $originCoords ? "{$originCoords['latitude']},{$originCoords['longitude']}" : null,
                'end_location' => $destinationCoords ? "{$destinationCoords['latitude']},{$destinationCoords['longitude']}" : null,
                'standby_photo_path' => $trip->kedatangan_muat_photo_path,
                'standby_photo_status' => $trip->kedatangan_muat_photo_status,
                'standby_photo_verified_by' => $trip->kedatangan_muat_photo_verified_by,
                'standby_photo_verified_at' => $trip->kedatangan_muat_photo_verified_at,
                'start_km_photo_path' => $trip->start_km_photo_path,
                'start_km_photo_status' => $trip->start_km_photo_status,
                'start_km_photo_verified_by' => $trip->start_km_photo_verified_by,
                'start_km_photo_verified_at' => $trip->start_km_photo_verified_at,
                'end_km_photo_path' => $trip->end_km_photo_path,
                'end_km_photo_status' => $trip->end_km_photo_status,
                'end_km_photo_verified_by' => $trip->end_km_photo_verified_by,
                'end_km_photo_verified_at' => $trip->end_km_photo_verified_at,
                'status_vehicle_location' => 'selesai',
                'status_lokasi' => null,
            ]);
        }
    }

    public function openRejectionModal($tripId, $photoType)
    {
        $this->rejectionTripId = $tripId;
        $this->rejectionPhotoType = $photoType;
        $this->rejectionReason = '';
        $this->showRejectionModal = true;
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->reset(['rejectionTripId', 'rejectionPhotoType', 'rejectionReason']);
    }

    public function rejectPhoto()
    {
        $this->validate(['rejectionReason' => 'required|string|min:10']);

        DB::transaction(function () {
            $trip = Trip::findOrFail($this->rejectionTripId);
            $photoType = $this->rejectionPhotoType;
            $statusField = "{$photoType}_status";
            if ($trip->{$statusField} !== 'pending') {
                session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi oleh admin lain.');
                $this->closeRejectionModal();
                return;
            }
            $trip->update([
                "{$this->rejectionPhotoType}_status" => 'rejected',
                "{$this->rejectionPhotoType}_verified_by" => Auth::id(),
                "{$this->rejectionPhotoType}_verified_at" => now(),
                "{$this->rejectionPhotoType}_rejection_reason" => $this->rejectionReason,
            ]);

            $this->updateTripStatus($trip->fresh());
            session()->flash('message', 'Photo has been rejected with a reason.');
        });
        $this->closeRejectionModal();
    }


    public function openModal()
    {
        $this->resetInputFields();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->tripId = null;
        $this->projectName = '';
        $this->userId = null; // Reset driver
        $this->jenisTrip = 'muatan perusahan'; // Reset jenis trip
        $this->slot_time = null;
        $this->jenis_berat = 'CDDL';

        $this->origin_address = '';
        $this->origin_link = '';
        $this->destination_address = '';
        $this->destination_link = '';
        $this->resetErrorBag();
    }

    /**
     * Membuka modal perbandingan surat jalan.
     */
    public function openDeliveryLetterModal($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $deliveryData = $trip->delivery_letter_path ?? [];

        $this->initialLetters = $deliveryData['initial_letters'] ?? [];
        $this->finalLetters = $deliveryData['final_letters'] ?? [];

        $this->currentInitialIndex = 0;
        $this->currentFinalIndex = 0;

        $this->showDeliveryLetterModal = true;
    }

    /**
     * Menutup modal perbandingan surat jalan.
     */
    public function closeDeliveryLetterModal()
    {
        $this->showDeliveryLetterModal = false;
        $this->initialLetters = [];
        $this->finalLetters = [];
    }

    public function nextInitialLetter()
    {
        if ($this->currentInitialIndex < count($this->initialLetters) - 1) {
            $this->currentInitialIndex++;
        }
    }

    public function previousInitialLetter()
    {
        if ($this->currentInitialIndex > 0) {
            $this->currentInitialIndex--;
        }
    }

    public function nextFinalLetter()
    {
        if ($this->currentFinalIndex < count($this->finalLetters) - 1) {
            $this->currentFinalIndex++;
        }
    }

    public function previousFinalLetter()
    {
        if ($this->currentFinalIndex > 0) {
            $this->currentFinalIndex--;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function sortByDetail($field)
    {
        if ($this->detailSortField === $field) {
            $this->detailSortDirection = $this->detailSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->detailSortDirection = 'asc';
        }
        $this->detailSortField = $field;
    }

    public function save()
    {
        $this->validate([
            'projectName' => 'required|string|max:255',
            'userId' => [
                'required',
                'exists:users,id',
                Rule::unique('trips', 'user_id')->where(function ($query) {
                    return $query->where('status_trip', 'proses');
                })->ignore($this->tripId),
            ],
            'jenisTrip' => 'required|in:muatan driver,muatan perusahan',
            'slot_time' => 'required|date_format:H:i',
            'jenis_berat' => 'required|in:CDDL,CDDS,CDE',

            'origin_address' => 'required|string|max:255',
            'origin_link' => 'required|url',
            'destination_address' => 'required|string|max:255',
            'destination_link' => 'required|url',
        ], [
            'userId.unique' => 'The selected driver already has an active trip in process.',
        ]);

        Trip::updateOrCreate(['id' => $this->tripId], [
            'project_name' => $this->projectName,
            'user_id' => $this->userId,
            'jenis_trip' => $this->jenisTrip,
            'slot_time' => $this->slot_time,
            'jenis_berat' => $this->jenis_berat,
            'origin' => [
                'address' => $this->origin_address,
                'link' => $this->origin_link,
            ],
            'destination' => [
                'address' => $this->destination_address,
                'link' => $this->destination_link,
            ],
            'status_trip' => $this->tripId ? Trip::find($this->tripId)->status_trip : 'tersedia',
        ]);

        session()->flash('message', $this->tripId ? 'Trip Updated Successfully.' : 'Trip Created Successfully.');

        $this->closeModal();
    }

    public function edit($id)
    {
        $trip = Trip::findOrFail($id);

        if ($trip->status_trip !== 'tersedia') {
            session()->flash('error', 'You can only edit trips that are available.');
            return;
        }

        $this->tripId = $id;
        $this->projectName = $trip->project_name;
        $this->userId = $trip->user_id; // Load driver
        $this->jenisTrip = $trip->jenis_trip; // Load jenis trip
        $this->slot_time = $trip->slot_time;
        $this->jenis_berat = $trip->getAttribute('jenis_berat');

        $origin = is_string($trip->origin) ? json_decode($trip->origin, true) : $trip->origin;
        $destination = is_string($trip->destination) ? json_decode($trip->destination, true) : $trip->destination;

        if (is_array($origin)) {
            $this->origin_address = $origin['address'] ?? '';
            $this->origin_link = $origin['link'] ?? '';
        }

        if (is_array($destination)) {
            $this->destination_address = $destination['address'] ?? '';
            $this->destination_link = $destination['link'] ?? '';
        }
        $this->openModal();
    }

    public function delete($id)
    {
        $trip = Trip::find($id);
        if (!$trip) return;

        if ($trip->status_trip !== 'tersedia') {
            session()->flash('error', 'Hanya bisa menghapus trip yang berstatus "Tersedia".');
            return;
        }

        $trip->delete();
        session()->flash('message', 'Trip Berhasil Dihapus.');
    }

    public function openImageModal($url)
    {
        $this->imageUrl = $url;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->showImageModal = false;
        $this->imageUrl = null;
    }

    public function render()
    {
        $trips = Trip::with('user', 'vehicle')
            ->where(function ($query) {
                $query->where('project_name', 'like', '%' . $this->search . '%')
                    ->orWhere('origin', 'like', '%' . $this->search . '%')
                    ->orWhere('destination', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $detailTripsQuery = Trip::with('user', 'vehicle')
            ->where(function ($query) {
                $query->where('project_name', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('origin', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('destination', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('status_trip', 'like', '%' . $this->detailSearch . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->detailSearch . '%');
                    })
                    ->orWhereHas('vehicle', function ($q) {
                        $q->where('license_plate', 'like', '%' . $this->detailSearch . '%');
                    });
            })->whereIn('status_trip', ['proses', 'selesai', 'revisi gambar', 'verifikasi gambar']);

        if ($this->detailSortField === 'user.name') {
            $detailTripsQuery->join('users', 'trips.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->detailSortDirection)
                ->select('trips.*');
        } else {
            $detailTripsQuery->orderBy($this->detailSortField, $this->detailSortDirection);
        }

        $detailTrips = $detailTripsQuery->paginate($this->detailPerPage, ['*'], 'detailPage');

        return view('livewire.tripTable.trip-table', [
            'trips' => $trips,
            'detailTrips' => $detailTrips,
        ]);
    }
}
