<?php

namespace App\Livewire;

use App\Models\Lembur;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Overtime Management')]
class LemburTable extends Component
{
    use WithPagination, WithFileUploads;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'tanggal_lembur';
    public $sortDirection = 'desc';

    // --- PENAMBAHAN: Properti untuk tabel detail ---
    public $detailPerPage = 5;
    public $detailSearch = '';
    public $detailSortField = 'jam_mulai_aktual';
    public $detailSortDirection = 'desc';
    public $showImageModal = false;
    public $imageUrl = '';

    // Properti untuk modal konfirmasi
    public $showConfirmModal = false;
    public $confirmingLemburId;
    public $confirmAction;
    public $confirmLevel;
    public $confirmMessage;
    public $alasan = '';
    public $file_path;

    // Properti untuk modal pratinjau PDF
    public $showPdfPreviewModal = false;
    public $lemburForPdf;

    public function updatingSearch()
    {
        $this->resetPage();
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

    public function askForConfirmation($lemburId, $action, $level)
    {
        $this->confirmingLemburId = $lemburId;
        $this->confirmAction = $action;
        $this->confirmLevel = $level;
        $actionText = $action === 'approve' ? 'menyetujui' : 'menolak';
        $this->confirmMessage = "Apakah Anda yakin ingin {$actionText} pengajuan lembur ini?";
        $this->showConfirmModal = true;
    }

    public function cancelConfirmation()
    {
        $this->reset(['showConfirmModal', 'confirmingLemburId', 'confirmAction', 'confirmLevel', 'confirmMessage', 'alasan', 'file_path']);
    }

    public function processAction()
    {
    $lembur = Lembur::findOrFail($this->confirmingLemburId);
    $user = Auth::user();

    // Sekarang hanya ada satu level: direksi
    $this->confirmLevel = 'direksi';
    $newStatus = $this->confirmAction === 'approve' ? 'Diterima' : 'Ditolak';

    // Langsung update persetujuan direksi
    $lembur->persetujuan_direksi = $newStatus;

    if ($this->confirmAction === 'approve') {
        $this->validate(['file_path' => 'required|file|mimes:pdf|max:2048']);

        if ($lembur->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lembur->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($lembur->file_path);
        }

        $ownerNameSlug = Str::slug($lembur->user->name);
        $tanggalLembur = \Carbon\Carbon::parse($lembur->tanggal_lembur)->format('d-m-Y');
        $fileName = "lembur-{$ownerNameSlug}-{$tanggalLembur}-{$lembur->uuid}.pdf";
        $lembur->file_path = $this->file_path->storeAs('lembur_files', $fileName, 'public');

        // Jika direksi setuju, status final langsung ke admin
        $lembur->status_lembur = 'Menunggu Konfirmasi Admin';

    } elseif ($this->confirmAction === 'reject') {
        $this->validate(['alasan' => 'required|string|min:10']);

        $lembur->alasan = 'Ditolak oleh ' . $user->getRoleNames()->first() . ': ' . $this->alasan;
        $lembur->status_lembur = 'Ditolak';

        // Hapus file jika ada saat penolakan
        if ($lembur->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lembur->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($lembur->file_path);
            $lembur->file_path = null;
        }
    }

    $lembur->save();
    session()->flash('message', 'Status lembur berhasil diperbarui.');
    $this->cancelConfirmation();
}

    public function askForAdminConfirmation($lemburId, $action)
    {
        $this->confirmingLemburId = $lemburId;
        $this->confirmAction = $action;
        $this->confirmLevel = 'admin';
        $actionText = $action === 'approve' ? 'menyelesaikan (final)' : 'menolak (final)';
        $this->confirmMessage = "Apakah Anda yakin ingin {$actionText} pengajuan lembur ini?";
        $this->showConfirmModal = true;
    }

    /**
     * PERBAIKAN: Logika persetujuan final oleh Admin.
     */
    public function processAdminAction()
    {
        $lembur = Lembur::findOrFail($this->confirmingLemburId);

        if ($this->confirmAction === 'approve') {
            $lembur->status_lembur = 'Diterima';
            $lembur->alasan = null; // Hapus alasan jika disetujui final
        } elseif ($this->confirmAction === 'reject') {
            $this->validate(['alasan' => 'required|string|min:10']);
            // Jika admin menolak, kembalikan status agar direksi bisa upload ulang.
            $lembur->status_lembur = 'Menunggu Persetujuan';
            $lembur->persetujuan_direksi = 'Menunggu Persetujuan'; // Reset status direksi
            $lembur->alasan = 'Ditolak oleh Admin (perlu revisi): ' . $this->alasan;
        }

        $lembur->save();
        session()->flash('message', 'Status lembur berhasil dikonfirmasi oleh Admin.');
        $this->cancelConfirmation();
    }

    public function showPdfPreview($lemburId)
    {
        $this->lemburForPdf = Lembur::with('user')->findOrFail($lemburId);
        $this->showPdfPreviewModal = true;
    }

    public function closePdfPreview()
    {
        $this->showPdfPreviewModal = false;
        $this->lemburForPdf = null;
    }

    public function render()
    {
        $lemburs = Lembur::with('user')
            ->where(function ($query) {
                $query->where('keterangan_lembur', 'like', '%' . $this->search . '%')
                    ->orWhere('department', 'like', '%' . $this->search . '%')
                    ->orWhere('jenis_hari', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Query untuk tabel kedua: Detail Pelaksanaan Lembur (yang sudah dimulai)
        $lemburDetails = Lembur::with('user')
            ->where('status_lembur', 'Diterima') // Hanya ambil yang sudah clock-in
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->detailSearch . '%');
                });
            })
            ->orderBy($this->detailSortField, $this->detailSortDirection)
            ->paginate($this->detailPerPage, ['*'], 'detailPage'); // Beri nama paginator

        return view('livewire.lemburTable.lembur-table', [
            'lemburs' => $lemburs,
            'lemburDetails' => $lemburDetails // Kirim data kedua ke view
        ]);
    }

    // --- PENAMBAHAN: Metode untuk sorting tabel detail ---
    public function sortByDetail($field)
    {
        if ($this->detailSortField === $field) {
            $this->detailSortDirection = $this->detailSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->detailSortDirection = 'asc';
        }
        $this->detailSortField = $field;
    }

    // --- PENAMBAHAN: Metode untuk menampilkan modal foto ---
    public function openImageModal($url)
    {
        $this->imageUrl = $url;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->showImageModal = false;
        $this->imageUrl = '';
    }

    // --- PENAMBAHAN: Metode untuk kalkulasi durasi ---
    public function calculateDuration($startTime, $endTime)
    {
        if (!$startTime || !$endTime) {
            return '-';
        }
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);
        return $start->diff($end)->format('%H Jam %I Menit');
    }
}
