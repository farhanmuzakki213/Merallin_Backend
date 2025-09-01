<?php

namespace App\Livewire;

use App\Models\BbmKendaraan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('BBM Kendaraan Management')]
class BbmKendaraanTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showImageModal = false;
    public $imageUrl;

    public $showRejectionModal = false;
    public $rejectionBbmId;
    public $rejectionPhotoType;
    public $rejectionReason = '';

    private function updateBbmOverallStatus(BbmKendaraan $bbm)
    {
        // 1. Kumpulkan status HANYA dari foto yang sudah diunggah (path-nya tidak kosong)
        $submittedStatuses = [];
        if ($bbm->start_km_photo_path) {
            $submittedStatuses[] = $bbm->start_km_photo_status;
        }
        if ($bbm->end_km_photo_path) {
            $submittedStatuses[] = $bbm->end_km_photo_status;
        }
        if ($bbm->nota_pengisian_photo_path) {
            $submittedStatuses[] = $bbm->nota_pengisian_photo_status;
        }

        // Jika belum ada foto sama sekali, statusnya adalah 'proses'
        if (empty($submittedStatuses)) {
            $bbm->status_bbm_kendaraan = 'proses';
            $bbm->save();
            return;
        }

        // 2. Tentukan status utama berdasarkan prioritas

        // Prioritas 1: Jika ada SATU SAJA yang ditolak, statusnya 'revisi gambar'.
        if (in_array('rejected', $submittedStatuses)) {
            $bbm->status_bbm_kendaraan = 'revisi gambar';
        }
        // Prioritas 2: Status 'selesai' HANYA jika SEMUA foto sudah diunggah DAN semuanya 'approved'.
        elseif (
            $bbm->start_km_photo_path && $bbm->end_km_photo_path && $bbm->nota_pengisian_photo_path &&
            !in_array('pending', $submittedStatuses) && !in_array('rejected', $submittedStatuses)
        ) {
            $bbm->status_bbm_kendaraan = 'selesai';
        }
        // Prioritas 3: Jika ada SATU SAJA yang masih 'pending', statusnya 'verifikasi gambar'.
        elseif (in_array('pending', $submittedStatuses)) {
            $bbm->status_bbm_kendaraan = 'verifikasi gambar';
        }
        // Prioritas 4 (Fallback): Jika tidak ada yang ditolak/pending, tapi belum semua diunggah.
        // Ini adalah skenario Anda: start_km 'approved', tapi end_km & nota belum ada.
        else {
            $bbm->status_bbm_kendaraan = 'proses';
        }

        $bbm->save();
    }


    public function approvePhoto($bbmId, $photoType)
    {
        $bbm = BbmKendaraan::findOrFail($bbmId);
        $statusField = "{$photoType}_status";
        if ($bbm->{$statusField} !== 'pending') {
            session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi.');
            return;
        }
        $bbm->update([
            "{$photoType}_status" => 'approved',
            "{$photoType}_verified_by" => Auth::id(),
            "{$photoType}_verified_at" => now(),
            "{$photoType}_rejection_reason" => null,
        ]);
        $this->updateBbmOverallStatus($bbm->fresh());
        session()->flash('message', 'Foto berhasil disetujui.');
    }

    public function openRejectionModal($bbmId, $photoType)
    {
        $this->rejectionBbmId = $bbmId;
        $this->rejectionPhotoType = $photoType;
        $this->rejectionReason = '';
        $this->showRejectionModal = true;
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->reset(['rejectionBbmId', 'rejectionPhotoType', 'rejectionReason']);
    }

    public function rejectPhoto()
    {
        $this->validate(['rejectionReason' => 'required|string|min:10']);

        $bbm = BbmKendaraan::findOrFail($this->rejectionBbmId);
        $statusField = "{$this->rejectionPhotoType}_status";
        if ($bbm->{$statusField} !== 'pending') {
            session()->flash('error', 'Gagal: Gambar ini sudah diverifikasi.');
            $this->closeRejectionModal();
            return;
        }
        $bbm->update([
            "{$this->rejectionPhotoType}_status" => 'rejected',
            "{$this->rejectionPhotoType}_verified_by" => Auth::id(),
            "{$this->rejectionPhotoType}_verified_at" => now(),
            "{$this->rejectionPhotoType}_rejection_reason" => $this->rejectionReason,
        ]);

        $this->updateBbmOverallStatus($bbm->fresh());
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
        $query = BbmKendaraan::with(['user', 'vehicle'])
            ->where(function ($q) {
                $q->whereHas('user', function ($subq) {
                    $subq->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('vehicle', function ($subq) {
                    $subq->where('license_plate', 'like', '%' . $this->search . '%');
                });
            });

        if ($this->sortField === 'user.name') {
            $query->join('users', 'bbm_kendaraan.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->sortDirection)
                ->select('bbm_kendaraan.*');
        } elseif ($this->sortField === 'vehicle.license_plate') {
            $query->join('vehicles', 'bbm_kendaraan.vehicle_id', '=', 'vehicles.id')
                ->orderBy('vehicles.license_plate', $this->sortDirection)
                ->select('bbm_kendaraan.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $bbmRecords = $query->paginate($this->perPage);

        return view('livewire.bbmKendaraanTable.bbm-kendaraan-table', [
            'bbmRecords' => $bbmRecords
        ]);
    }
}
