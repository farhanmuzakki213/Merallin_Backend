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
    public $projectName, $origin, $destination;

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

    public $showBongkarPhotoModal = false;
    public $bongkarPhotos = [];
    public $currentBongkarPhotoIndex = 0;

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
                SUM(CASE WHEN jenis_trip = 'muatan perusahaan' THEN 1 ELSE 0 END) as trip_perusahaan,
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

    /**
     * Membuka modal galeri untuk foto bongkar.
     */
    public function openBongkarPhotoModal($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $this->bongkarPhotos = $trip->bongkar_photo_path ?? [];
        $this->currentBongkarPhotoIndex = 0;
        $this->showBongkarPhotoModal = true;
    }

    /**
     * Menutup modal galeri untuk foto bongkar.
     */
    public function closeBongkarPhotoModal()
    {
        $this->showBongkarPhotoModal = false;
        $this->bongkarPhotos = [];
    }

    /**
     * Menampilkan foto berikutnya di galeri.
     */
    public function nextBongkarPhoto()
    {
        if ($this->currentBongkarPhotoIndex < count($this->bongkarPhotos) - 1) {
            $this->currentBongkarPhotoIndex++;
        }
    }

    /**
     * Menampilkan foto sebelumnya di galeri.
     */
    public function previousBongkarPhoto()
    {
        if ($this->currentBongkarPhotoIndex > 0) {
            $this->currentBongkarPhotoIndex--;
        }
    }

    /**
     * Load data driver saat komponen di-mount.
     */
    public function mount()
    {
        // Asumsi driver memiliki role 'driver'. Sesuaikan jika perlu.
        $this->drivers = User::whereHas('roles', function ($query) {
            $query->where('name', 'driver');
        })->get();
    }

    public function approvePhoto($tripId, $photoType)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update([
            "{$photoType}_status" => 'approved',
            "{$photoType}_verified_by" => Auth::id(),
            "{$photoType}_verified_at" => now(),
            "{$photoType}_rejection_reason" => null,
        ]);
        $trip->refresh();
        $initialLetterApproved = !isset($trip->delivery_letter_path['initial_letters']) || $trip->delivery_letter_initial_status === 'approved';
        $finalLetterApproved = !isset($trip->delivery_letter_path['final_letters']) || $trip->delivery_letter_final_status === 'approved';

        $allDocumentsApproved = $trip->start_km_photo_path && $trip->start_km_photo_status === 'approved' &&
            $trip->muat_photo_path && $trip->muat_photo_status === 'approved' &&
            $trip->bongkar_photo_path && $trip->bongkar_photo_status === 'approved' &&
            $trip->end_km_photo_path && $trip->end_km_photo_status === 'approved' &&
            $trip->delivery_letter_path && $initialLetterApproved && $finalLetterApproved &&
            $trip->delivery_order_path && $trip->delivery_order_status === 'approved' &&
            $trip->timbangan_kendaraan_photo_path && $trip->timbangan_kendaraan_photo_status === 'approved' &&
            $trip->segel_photo_path && $trip->segel_photo_status === 'approved';

        if ($allDocumentsApproved) {
            $trip->update(['status_trip' => 'selesai']);
            session()->flash('message', 'Photo approved. All documents complete, Trip is now finished!');
        } else {
            session()->flash('message', 'Photo has been approved.');
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

        $trip = Trip::findOrFail($this->rejectionTripId);
        $trip->update([
            "{$this->rejectionPhotoType}_status" => 'rejected',
            "{$this->rejectionPhotoType}_verified_by" => Auth::id(),
            "{$this->rejectionPhotoType}_verified_at" => now(),
            "{$this->rejectionPhotoType}_rejection_reason" => $this->rejectionReason,
        ]);

        session()->flash('message', 'Photo has been rejected with a reason.');
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
        $this->origin = '';
        $this->destination = '';
        $this->userId = null; // Reset driver
        $this->jenisTrip = 'muatan perusahan'; // Reset jenis trip
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
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'userId' => [
                'required',
                'exists:users,id',
                Rule::unique('trips', 'user_id')->where(function ($query) {
                    return $query->where('status_trip', 'proses');
                })->ignore($this->tripId),
            ],
            'jenisTrip' => 'required|in:muatan driver,muatan perusahan',
        ], [
            'userId.unique' => 'The selected driver already has an active trip in process.',
        ]);

        Trip::updateOrCreate(['id' => $this->tripId], [
            'project_name' => $this->projectName,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'user_id' => $this->userId,
            'jenis_trip' => $this->jenisTrip,
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
        $this->origin = $trip->origin;
        $this->destination = $trip->destination;
        $this->userId = $trip->user_id; // Load driver
        $this->jenisTrip = $trip->jenis_trip; // Load jenis trip
        $this->openModal();
    }

    public function delete($id)
    {
        $trip = Trip::find($id);
        if ($trip) {
            $trip->delete();
            if ($trip->status_trip !== 'tersedia') {
                session()->flash('error', 'You can only delete trips that are available.');
                return;
            }
            session()->flash('message', 'Trip Deleted Successfully.');
        }
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
        $trips = Trip::with('user')
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

        $detailTripsQuery = Trip::with('user')
            ->where(function ($query) {
                $query->where('project_name', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('origin', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('destination', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('license_plate', 'like', '%' . $this->detailSearch . '%')
                    ->orWhere('status_trip', 'like', '%' . $this->detailSearch . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->detailSearch . '%');
                    });
            })->whereIn('status_trip', ['proses', 'selesai']);

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
