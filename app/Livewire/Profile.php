<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
#[Title('User Profile')]
class Profile extends Component
{
    use WithFileUploads;

    // Properties matching your schema
    public string $name = '';
    public string $email = '';
    public ?string $alamat = '';
    public ?string $no_telepon = '';

    // Temporary property for the new photo
    public $photo;

    public function mount(): void
    {
        $user = Auth::user();
        $this->fill(
            $user->only('name', 'email', 'alamat', 'no_telepon'),
        );
    }

    public function save(): void
    {
        $validatedData = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:1024'], // 1MB Max
        ]);

        if ($this->photo) {
            $validatedData['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
        }

        // The 'photo' property is temporary and should not be saved to the user model
        unset($validatedData['photo']);

        Auth::user()->update($validatedData);

        session()->flash('message', 'Profile successfully updated.');

        $this->dispatch('profile-saved');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
