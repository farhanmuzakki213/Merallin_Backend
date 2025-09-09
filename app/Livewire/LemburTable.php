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

        // REVISI 1: Logika Bertingkat untuk Direksi
        // Direksi hanya bisa approve jika Manajer sudah approve.
        if ($this->confirmLevel === 'direksi' && $lembur->persetujuan_manajer !== 'Diterima') {
            session()->flash('error', 'Persetujuan harus melalui Manajer terlebih dahulu.');
            $this->cancelConfirmation();
            return;
        }

        $newStatus = $this->confirmAction === 'approve' ? 'Diterima' : 'Ditolak';

        // Update status persetujuan untuk level yang bersangkutan
        if ($this->confirmLevel === 'manajer') {
            $lembur->persetujuan_manajer = $newStatus;
        } elseif ($this->confirmLevel === 'direksi') {
            $lembur->persetujuan_direksi = $newStatus;
        }

        if ($this->confirmAction === 'approve') {
            $this->validate(['file_path' => 'required|file|mimes:pdf|max:2048']);

            // Hapus file lama jika ada (penting saat direksi menimpa file manajer).
            if ($lembur->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lembur->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($lembur->file_path);
            }

            $ownerNameSlug = Str::slug($lembur->user->name);
            $tanggalLembur = \Carbon\Carbon::parse($lembur->tanggal_lembur)->format('d-m-Y');
            $fileName = "lembur-{$ownerNameSlug}-{$tanggalLembur}-{$lembur->uuid}.pdf";
            $lembur->file_path = $this->file_path->storeAs('lembur_files', $fileName, 'public');

        } elseif ($this->confirmAction === 'reject') {
            $this->validate(['alasan' => 'required|string|min:10']);

            // REVISI 2: Jika salah satu menolak, semua status menjadi Ditolak.
            $lembur->alasan = 'Ditolak oleh ' . $user->getRoleNames()->first() . ': ' . $this->alasan;
            $lembur->persetujuan_manajer = 'Ditolak';
            $lembur->persetujuan_direksi = 'Ditolak';
            $lembur->status_lembur = 'Ditolak';

            if ($lembur->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lembur->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($lembur->file_path);
                $lembur->file_path = null; // Kosongkan path di database
            }
        }

        // Tentukan status final HANYA JIKA belum ditolak
        if ($lembur->status_lembur !== 'Ditolak') {
            if ($lembur->persetujuan_manajer === 'Diterima' && $lembur->persetujuan_direksi === 'Diterima') {
                $lembur->status_lembur = 'Menunggu Konfirmasi Admin';
            } else {
                $lembur->status_lembur = 'Menunggu Persetujuan';
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

        return view('livewire.lemburTable.lembur-table', [
            'lemburs' => $lemburs
        ]);
    }
}
