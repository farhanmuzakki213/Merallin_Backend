<?php

namespace App\Livewire;

use App\Models\VehicleLocation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Verifikasi Lokasi Kendaraan')]
class VehicleLocationTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showImageModal = false;
    public $imageUrl;

    public $showRejectionModal = false;
    public $rejectionLocationId;
    public $rejectionPhotoType;
    public $rejectionReason = '';

    private function updateOverallStatus(VehicleLocation $location)
    {
        $statuses = array_filter([
            $location->standby_photo_status,
            $location->start_km_photo_status,
            $location->end_km_photo_status,
        ]);

        if (in_array('rejected', $statuses)) {
            $location->status_vehicle_location = 'revisi gambar';
        } elseif (in_array('pending', $statuses)) {
            $location->status_vehicle_location = 'verifikasi gambar';
        } else {
            $unapprovedStatuses = array_filter($statuses, function ($status) {
                return $status !== 'approved';
            });
            if (empty($unapprovedStatuses)) {
                $location->status_vehicle_location = 'selesai';
            } else {
                $location->status_vehicle_location = 'proses';
            }
        }
        $location->save();
    }

    public function approvePhoto($locationId, $photoType)
    {
        $location = VehicleLocation::findOrFail($locationId);
        $statusField = "{$photoType}_status";
        if ($location->{$statusField} !== 'pending') {
            session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi.');
            return;
        }
        $location->update([
            "{$photoType}_status" => 'approved',
            "{$photoType}_verified_by" => Auth::id(),
            "{$photoType}_verified_at" => now(),
            "{$photoType}_rejection_reason" => null,
        ]);
        $this->updateOverallStatus($location->fresh());
        session()->flash('message', 'Foto berhasil disetujui.');
    }

    public function openRejectionModal($locationId, $photoType)
    {
        $this->rejectionLocationId = $locationId;
        $this->rejectionPhotoType = $photoType;
        $this->rejectionReason = '';
        $this->showRejectionModal = true;
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->reset(['rejectionLocationId', 'rejectionPhotoType', 'rejectionReason']);
    }

    public function rejectPhoto()
    {
        $this->validate(['rejectionReason' => 'required|string|min:10']);
        $location = VehicleLocation::findOrFail($this->rejectionLocationId);
        $statusField = "{$this->rejectionPhotoType}_status";
        if ($location->{$statusField} !== 'pending') {
            session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi.');
            $this->closeRejectionModal();
            return;
        }
        $location->update([
            "{$this->rejectionPhotoType}_status" => 'rejected',
            "{$this->rejectionPhotoType}_verified_by" => Auth::id(),
            "{$this->rejectionPhotoType}_verified_at" => now(),
            "{$this->rejectionPhotoType}_rejection_reason" => $this->rejectionReason,
        ]);
        $this->updateOverallStatus($location->fresh());
        session()->flash('message', 'Foto berhasil ditolak.');
        $this->closeRejectionModal();
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
        $query = VehicleLocation::with(['user', 'vehicle'])
            ->where(function ($q) {
                $q->where('keterangan', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('vehicle', fn($sq) => $sq->where('license_plate', 'like', '%' . $this->search . '%'));
            });

        $locations = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.vehicleLocationTable.vehicle-location-table', [
            'locations' => $locations,
        ]);
    }
}
