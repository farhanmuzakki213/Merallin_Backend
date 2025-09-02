<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordForm extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the user's password.
     */
    public function updatePassword(): void
    {
        // Validasi input
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        // Update password pengguna
        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Reset field setelah berhasil
        $this->reset('current_password', 'password', 'password_confirmation');

        // Kirim pesan sukses
        session()->flash('password-message', 'Password successfully updated.');

        // Kirim event untuk menutup modal
        $this->dispatch('password-updated');
    }


    public function render()
    {
        return view('livewire.update-password-form');
    }
}
