<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Users Data Table')]
class UserTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Properti untuk modal
    public $showModal = false;
    public $userId;
    public $name, $email, $alamat, $no_telepon;
    public $password, $password_confirmation;
    public $userRoles = [];
    public $allRoles = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string',
            'userRoles' => 'required|array|min:1',
            'userRoles.*' => 'string|exists:roles,name',
            'password' => 'nullable|min:8|confirmed',
        ];
    }

    public function mount()
    {
        $this->allRoles = Role::pluck('name')->all();
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

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->alamat = $user->alamat;
        $this->no_telepon = $user->no_telepon;
        $this->userRoles = $user->getRoleNames()->toArray();
        $this->password = '';
        $this->password_confirmation = '';

        $this->showModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'alamat' => $validatedData['alamat'],
            'no_telepon' => $validatedData['no_telepon'],
        ]);

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();
        $user->syncRoles($validatedData['userRoles']);

        session()->flash('message', 'User successfully updated.');
        $this->closeModal();
    }

    public function delete($userId)
    {
        $user = User::findOrFail($userId);
        // Mencegah user menghapus dirinya sendiri
        if ($user->id == auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }
        $user->delete();
        session()->flash('message', 'User successfully deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'name', 'email', 'alamat', 'no_telepon', 'password', 'password_confirmation', 'userRoles']);
    }

    public function render()
    {
        $users = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('alamat', 'like', '%' . $this->search . '%')
                    ->orWhere('no_telepon', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.userTable.user-table', [
            'users' => $users
        ]);
    }
}
