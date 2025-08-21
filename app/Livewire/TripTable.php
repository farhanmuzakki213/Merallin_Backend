<?php

namespace App\Livewire;

use App\Models\Trip;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

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
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate([
            'projectName' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        Trip::updateOrCreate(['id' => $this->tripId], [
            'project_name' => $this->projectName,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'status_trip' => 'tersedia',
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
