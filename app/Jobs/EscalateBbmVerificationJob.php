<?php

namespace App\Jobs;

use App\Models\BbmKendaraan;
use App\Models\User;
use App\Notifications\BbmPhotoVerificationRequired;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class EscalateBbmVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bbmKendaraan;
    protected $photoDisplayName;
    protected $vehicleInfo;
    protected $publicPhotoUrl;
    protected $roleToNotify;
    protected $photoTypeStatus;

    public function __construct(BbmKendaraan $bbmKendaraan, string $photoDisplayName, string $vehicleInfo, string $publicPhotoUrl, string $roleToNotify, string $photoTypeStatus)
    {
        $this->bbmKendaraan = $bbmKendaraan;
        $this->photoDisplayName = $photoDisplayName;
        $this->vehicleInfo = $vehicleInfo;
        $this->publicPhotoUrl = $publicPhotoUrl;
        $this->roleToNotify = $roleToNotify;
        $this->photoTypeStatus = $photoTypeStatus;
    }

    public function handle(): void
    {
        $this->bbmKendaraan->refresh();

        // Cek apakah status foto masih 'pending'
        if ($this->bbmKendaraan->{$this->photoTypeStatus} !== 'pending') {
            return; // Jika sudah diverifikasi, hentikan job
        }

        $usersToNotify = User::role($this->roleToNotify)->whereHas('pushSubscriptions')->get();

        if ($usersToNotify->isNotEmpty()) {
            $eskalasiTitle = "ESKALASI: Verifikasi Foto BBM Tertunda";
            $eskalasiMessage = "Foto '{$this->photoDisplayName}' untuk kendaraan '{$this->vehicleInfo}' belum diverifikasi. Mohon segera ditindaklanjuti.";

            Notification::send($usersToNotify, new BbmPhotoVerificationRequired(
                $this->bbmKendaraan,
                $eskalasiMessage,
                $this->vehicleInfo,
                $this->publicPhotoUrl,
                $eskalasiTitle
            ));
        }
    }
}
