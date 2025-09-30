<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Users Data Table')]
class UserTable extends Component
{
    use WithPagination, WithFileUploads;

    // Properti untuk Tabel
    public $perPage = 10;
    public $search = '';
    public $sortField = 'name'; // Default sort field
    public $sortDirection = 'asc'; // Default sort direction

    // Properti untuk Modal
    public $showModal = false;
    public $userId;
    public $name, $email, $alamat, $no_telepon, $nik, $gaji_pokok;
    public $password, $password_confirmation;
    public $userRoles = [];
    public $allRoles;
    public $idCardFile;
    public $existingIdCard;

    public function mount()
    {
        $this->allRoles = Role::pluck('name')->all();
    }

    // Mengikuti pola TripTable
    private function resetInputFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->nik = '';
        $this->alamat = '';
        $this->no_telepon = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->userRoles = [];
        $this->idCardFile = null;
        $this->existingIdCard = null;
        $this->resetErrorBag();
    }

    // Mengikuti pola TripTable
    public function openModal()
    {
        $this->resetInputFields();
        $this->showModal = true;
    }

    // Mengikuti pola TripTable
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    // Mengikuti pola TripTable
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function save()
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'nik' => ['required', 'string', 'digits:16', Rule::unique('users')->ignore($this->userId)],
            'alamat' => 'nullable|string|max:500',
            'no_telepon' => 'nullable|string|max:15',
            'userRoles' => 'required|array|min:1',
            'gaji_pokok' => 'sometimes|numeric|min:0',
            'password' => [$this->userId ? 'nullable' : 'required', 'min:8', 'confirmed'],
            'idCardFile' => [$this->userId ? 'nullable' : 'sometimes', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'nik' => $this->nik,
            'alamat' => $this->alamat,
            'no_telepon' => $this->no_telepon,
        ];

        if (!empty($this->gaji_pokok)) {
            $data['gaji_pokok'] = $this->gaji_pokok;
        }

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->idCardFile) {
            if ($this->userId) {
                $oldUser = User::find($this->userId);
                if ($oldUser->id_card_path && Storage::disk('public')->exists($oldUser->id_card_path)) {
                    Storage::disk('public')->delete($oldUser->id_card_path);
                }
            }
            $fileName = 'id-card-' . Str::slug($this->name) . '-' . uniqid() . '.' . $this->idCardFile->getClientOriginalExtension();
            $data['id_card_path'] = $this->idCardFile->storeAs('id_cards', $fileName, 'public');
        }

        $user = User::updateOrCreate(['id' => $this->userId], $data);
        $user->syncRoles($this->userRoles);

        session()->flash('message', 'User successfully ' . ($this->userId ? 'updated.' : 'created.'));
        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->nik = $user->nik;
        $this->alamat = $user->alamat;
        $this->no_telepon = $user->no_telepon;
        $this->userRoles = $user->getRoleNames()->toArray();
        $this->existingIdCard = $user->original_id_card_name;
        $this->gaji_pokok = $user->gaji_pokok;
        $this->idCardFile = null;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        if ($user->id == auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        if ($user->id_card_path && Storage::disk('public')->exists($user->id_card_path)) {
            Storage::disk('public')->delete($user->id_card_path);
        }

        $user->delete();
        session()->flash('message', 'User successfully deleted.');
    }

    // Mengikuti pola TripTable
    public function render()
    {
        $searchTerm = strtolower($this->search);
        $users = User::with('roles')
            ->where(function ($query) use ($searchTerm) {
                $query->where(DB::raw('LOWER(name)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw('LOWER(email)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw('LOWER(nik)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere(DB::raw('LOWER(alamat)'), 'like', '%' . $searchTerm . '%')
                    ->orWhere('no_telepon', 'like', '%' . $this->search . '%')
                    ->orWhere('gaji_pokok', 'like', '%' . $this->search . '%')
                    ->orWhereHas('roles', function ($q) use ($searchTerm) {
                        $q->where(DB::raw('LOWER(name)'), 'like', '%' . $searchTerm . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.userTable.user-table', [
            'users' => $users,
        ]);
    }
}
