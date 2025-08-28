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
    protected $photoDisplayName;
    protected $projectName;
    protected $publicPhotoUrl;
    protected $roleToNotify;
    protected $photoTypeStatus; // Menyimpan nama field status foto

    public function __construct(Trip $trip, string $photoDisplayName, string $projectName, string $publicPhotoUrl, string $roleToNotify, string $photoTypeStatus)
    {
        $this->trip = $trip;
        $this->photoDisplayName = $photoDisplayName;
        $this->projectName = $projectName;
        $this->publicPhotoUrl = $publicPhotoUrl;
        $this->roleToNotify = $roleToNotify;
        $this->photoTypeStatus = $photoTypeStatus;
    }

    public function handle(): void
    {
        $this->trip->refresh();

        // Cek apakah status foto masih 'pending'
        if ($this->trip->{$this->photoTypeStatus} !== 'pending') {
            return; // Jika sudah diverifikasi, hentikan job
        }

        // Ambil user berdasarkan peran yang sudah subscribe
        $usersToNotify = User::role($this->roleToNotify)->whereHas('pushSubscriptions')->get();

        if ($usersToNotify->isNotEmpty()) {
            // Pesan eskalasi yang lebih jelas
            $eskalasiTitle = "ESKALASI: Verifikasi Foto Tertunda";
            $eskalasiMessage = "Foto '{$this->photoDisplayName}' di proyek '{$this->projectName}' belum diverifikasi. Mohon segera ditindaklanjuti.";

            Notification::send($usersToNotify, new PhotoVerificationRequired(
                $this->trip,
                $eskalasiMessage, // Menggunakan pesan eskalasi
                $this->projectName,
                $this->publicPhotoUrl,
                $eskalasiTitle // Mengirimkan judul eskalasi
            ));
        }
    }
}
