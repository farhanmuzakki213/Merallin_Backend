<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    /**
     * Log the user out of the application.
     */
    public function logout()
    {
        Auth::logout();

        // Baris ini penting untuk keamanan
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Arahkan pengguna ke halaman login setelah logout
        return $this->redirect('/login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
