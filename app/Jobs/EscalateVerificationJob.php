<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Models\User;
use App\Notifications\PhotoVerificationRequired;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class EscalateVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trip;
    protected $photoType;
    protected $roleToNotify;

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip, string $photoType, string $roleToNotify)
    {
        $this->trip = $trip;
        $this->photoType = $photoType;
        $this->roleToNotify = $roleToNotify;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->trip->refresh();
        $statusField = $this->photoType . '_status';

        if ($this->trip->{$statusField} !== 'pending') {
            return;
        }

        // Ambil user yang sudah subscribe
        $usersToNotify = User::whereHas('roles', function ($query) {
            $query->where('name', $this->roleToNotify);
        })->whereHas('pushSubscriptions')->get();

        if ($usersToNotify->isNotEmpty() && $this->trip->user) {
            $photoName = ucwords(str_replace('_', ' ', $this->photoType));
            $message = "ESKALASI: Foto '{$photoName}' dari driver {$this->trip->user->name} belum diverifikasi.";

            // Gunakan loop foreach agar lebih aman
            foreach ($usersToNotify as $user) {
                $user->notify(new PhotoVerificationRequired($this->trip, $message));
            }
        }
    }
}
