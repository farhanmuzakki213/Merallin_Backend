<?php

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
#[Title('Vehicles Data Table')]
class VehicleTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'license_plate';
    public $sortDirection = 'asc';

    // Properti untuk modal
    public $showModal = false;
    public $vehicleId;
    public $license_plate, $model, $type;

    protected function rules()
    {
        return [
            'license_plate' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles')->ignore($this->vehicleId),
            ],
            'model' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
        ];
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
        $this->vehicleId = null;
        $this->license_plate = '';
        $this->model = '';
        $this->type = '';
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

    public function edit($vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $this->vehicleId = $vehicleId;
        $this->license_plate = $vehicle->license_plate;
        $this->model = $vehicle->model;
        $this->type = $vehicle->type;

        $this->showModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        Vehicle::updateOrCreate(['id' => $this->vehicleId], $validatedData);

        session()->flash('message', $this->vehicleId ? 'Vehicle successfully updated.' : 'Vehicle successfully created.');
        $this->closeModal();
    }

    public function delete($vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $vehicle->delete();
        session()->flash('message', 'Vehicle successfully deleted.');
    }


    public function render()
    {
        $searchTerm = strtolower($this->search);
        $vehicles = Vehicle::where(function ($query) use ($searchTerm) {
                $query->where(DB::raw('LOWER(license_plate)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw('LOWER(model)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw('LOWER(type)'), 'like', '%' . $searchTerm . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.vehicleTable.vehicle-table', [
            'vehicles' => $vehicles
        ]);
    }
}
