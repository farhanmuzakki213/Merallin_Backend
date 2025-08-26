<?php

namespace App\Livewire;

use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Vehicle Locations')]
class VehicleLocationTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'reported_at';
    public $sortDirection = 'desc';

    // Properti untuk modal
    public $showModal = false;
    public $locationId;
    public $vehicle_id, $user_id, $location, $event_type, $trip_id, $remarks, $reported_at;

    // Data untuk dropdown
    public $allVehicles = [];
    public $allDrivers = [];
    public $allTrips = [];

    /**
     * Menyiapkan data sebelum proses validasi.
     * Mengubah string kosong menjadi NULL untuk foreign key yang opsional.
     */
    protected function prepareForValidation($attributes)
    {
        // Jika trip_id kosong, ubah menjadi null
        if (empty($attributes['trip_id'])) {
            $attributes['trip_id'] = null;
        }

        // Jika user_id kosong, ubah menjadi null (praktik terbaik)
        if (empty($attributes['user_id'])) {
            $attributes['user_id'] = null;
        }

        return $attributes;
    }

    protected function rules()
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'nullable|exists:users,id',
            'location' => 'required|string|max:255',
            'event_type' => 'required|in:trip_completion,empty_return,manual_update',
            'trip_id' => 'nullable|exists:trips,id',
            'remarks' => 'nullable|string',
            'reported_at' => 'required|date',
        ];
    }

    /**
     * Load data yang dibutuhkan saat komponen di-mount.
     */
    public function mount()
    {
        $this->allVehicles = Vehicle::orderBy('license_plate')->get();
        $this->allDrivers = User::whereHas('roles', fn($q) => $q->where('name', 'driver'))->orderBy('name')->get();
        $this->allTrips = Trip::whereIn('status_trip', ['proses', 'selesai'])->orderBy('project_name')->get();
        $this->reported_at = now()->format('Y-m-d\TH:i');
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

    private function resetInputFields()
    {
        $this->locationId = null;
        $this->vehicle_id = '';
        $this->user_id = '';
        $this->location = '';
        $this->event_type = 'manual_update';
        $this->trip_id = '';
        $this->remarks = '';
        $this->reported_at = now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
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

    public function edit($id)
    {
        $location = VehicleLocation::findOrFail($id);
        $this->locationId = $id;
        $this->vehicle_id = $location->vehicle_id;
        $this->user_id = $location->user_id;
        $this->location = $location->location;
        $this->event_type = $location->event_type;
        $this->trip_id = $location->trip_id;
        $this->remarks = $location->remarks;
        $this->reported_at = $location->reported_at->format('Y-m-d\TH:i');

        $this->showModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        VehicleLocation::updateOrCreate(['id' => $this->locationId], $validatedData);

        session()->flash('message', $this->locationId ? 'Location successfully updated.' : 'Location successfully created.');
        $this->closeModal();
    }

    public function delete($id)
    {
        VehicleLocation::findOrFail($id)->delete();
        session()->flash('message', 'Location successfully deleted.');
    }

    public function render()
    {
        $locations = VehicleLocation::with(['vehicle', 'user', 'trip'])
            ->where(function ($query) {
                $query->where('location', 'like', '%' . $this->search . '%')
                    ->orWhere('remarks', 'like', '%' . $this->search . '%')
                    ->orWhereHas('vehicle', fn($q) => $q->where('license_plate', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.vehicleLocationTable.vehicle-location-table', [
            'locations' => $locations
        ]);
    }
}
