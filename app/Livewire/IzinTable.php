<?php

namespace App\Livewire;

use App\Models\Izin;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Employee Leave Management')]
class IzinTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Properti untuk modal gambar
    public $showImageModal = false;
    public $imageUrl;
    public $imageTitle; // Untuk menampilkan jenis izin di modal

    /**
     * Mengatur ulang halaman paginasi setiap kali ada pencarian baru.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Mengubah urutan sorting tabel.
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    /**
     * Membuka modal untuk menampilkan gambar bukti.
     *
     * @param string $url
     * @param string $title
     * @return void
     */
    public function openImageModal($url, $title)
    {
        $this->imageUrl = $url;
        $this->imageTitle = "Bukti Izin: " . $title;
        $this->showImageModal = true;
    }

    /**
     * Menutup modal gambar.
     *
     * @return void
     */
    public function closeImageModal()
    {
        $this->showImageModal = false;
        $this->imageUrl = null;
        $this->imageTitle = null;
    }

    /**
     * Merender komponen dan mengambil data dari database.
     */
    public function render()
    {
        $izins = Izin::with('user')
            ->where(function ($query) {
                $query->where('jenis_izin', 'like', '%' . $this->search . '%')
                    ->orWhere('alasan', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.izinTable.izin-table', [
            'izins' => $izins
        ]);
    }
}
