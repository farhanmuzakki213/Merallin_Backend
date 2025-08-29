<?php

namespace App\Jobs;

use App\Models\VehicleLocation;
use App\Models\User;
use App\Notifications\VehicleLocationPhotoVerificationRequired;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class EscalateVehicleLocationVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected VehicleLocation $location,
        protected string $photoDisplayName,
        protected string $vehicleInfo,
        protected string $publicPhotoUrl,
        protected string $roleToNotify,
        protected string $photoTypeStatus
    ) {}

    public function handle(): void
    {
        $this->location->refresh();

        if ($this->location->{$this->photoTypeStatus} !== 'pending') {
            return;
        }

        $users = User::role($this->roleToNotify)->whereHas('pushSubscriptions')->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new VehicleLocationPhotoVerificationRequired(
                $this->location,
                "ESKALASI: Foto '{$this->photoDisplayName}' untuk kendaraan '{$this->vehicleInfo}' belum diverifikasi.",
                $this->vehicleInfo,
                $this->publicPhotoUrl,
                "ESKALASI: Verifikasi Tertunda"
            ));
        }
    }
}
