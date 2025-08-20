<?php

namespace App\Livewire;

use App\Models\Lembur;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Overtime Management')]
class LemburTable extends Component
{
    use WithPagination;

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
        $this->reset(['showConfirmModal', 'confirmingLemburId', 'confirmAction', 'confirmLevel', 'confirmMessage']);
    }

    public function processAction()
    {
        $lembur = Lembur::findOrFail($this->confirmingLemburId);
        $newStatus = $this->confirmAction === 'approve' ? 'Diterima' : 'Ditolak';

        if ($this->confirmLevel === 'manajer') {
            $lembur->persetujuan_manajer = $newStatus;
        } elseif ($this->confirmLevel === 'direksi') {
            $lembur->persetujuan_direksi = $newStatus;
        }

        if ($lembur->persetujuan_manajer === 'Ditolak' || $lembur->persetujuan_direksi === 'Ditolak') {
            $lembur->status_lembur = 'Ditolak';
        } elseif ($lembur->persetujuan_manajer === 'Diterima' && $lembur->persetujuan_direksi === 'Diterima') {
            $lembur->status_lembur = 'Diterima';
        } else {
            $lembur->status_lembur = 'Menunggu Persetujuan';
        }

        $lembur->save();
        session()->flash('message', 'Status lembur berhasil diperbarui.');
        $this->cancelConfirmation();
    }

    /**
     * Menampilkan modal pratinjau PDF.
     */
    public function showPdfPreview($lemburId)
    {
        // Eager load relasi user untuk ditampilkan di PDF
        $this->lemburForPdf = Lembur::with('user')->findOrFail($lemburId);
        $this->showPdfPreviewModal = true;
    }

    /**
     * Menutup modal pratinjau PDF.
     */
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
