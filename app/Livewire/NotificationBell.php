<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;

    // Listener untuk event yang mungkin kita butuhkan nanti
    protected $listeners = ['notificationReceived' => '$refresh'];

    public function mount()
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications()
    {
        if (Auth::check()) {
            $this->unreadCount = Auth::user()->unreadNotifications()->count();
        }
    }

    public function markAsRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
            $this->unreadCount = 0; // Langsung update count di frontend
        }
    }

    public function render()
    {
        $notifications = [];
        if (Auth::check()) {
            // Kita ambil notifikasi langsung di dalam view agar lebih efisien saat polling
            $notifications = Auth::user()->notifications()->limit(7)->get();
        }

        return view('livewire.notification-bell', [
            'notifications' => $notifications
        ]);
    }
}
