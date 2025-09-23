<?php

namespace App\Livewire;

use App\Models\Izin;
use Illuminate\Support\Facades\DB;
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
        $searchTerm = strtolower($this->search);
        $izins = Izin::with('user')
            ->where(function ($query) use ($searchTerm) {
                $query->where(DB::raw('LOWER(jenis_izin)'), 'like', '%' . $searchTerm  . '%')
                    ->orWhere(DB::raw('LOWER(alasan)'), 'like', '%' . $searchTerm  . '%')
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where(DB::raw('LOWER(name)'), 'like', '%' . $searchTerm  . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.izinTable.izin-table', [
            'izins' => $izins
        ]);
    }
}
