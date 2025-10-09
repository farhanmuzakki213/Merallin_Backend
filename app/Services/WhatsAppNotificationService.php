<?php

namespace App\Services;

use App\Jobs\SendWhatsappNotificationJob;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppNotificationService
{
    protected $groupId;

    public function __construct()
    {
        $this->groupId = config('services.whatsapp.group_id');
    }

    /**
     * Fungsi inti untuk memasukkan job pengiriman ke antrian.
     */
    private function dispatchWhatsappJob(string $to, string $message, ?string $imageUrl = null, ?int $tripId = null): void
    {
        $payload = [
            'to' => $to,
            'message' => $message,
            'image_url' => $imageUrl,
        ];
        SendWhatsappNotificationJob::dispatch($payload, $tripId);
    }

    // ===================================================================
    // METODE UNTUK TEST (SESUAI PERMINTAAN)
    // ===================================================================

    public function testSendPersonalMessage(?string $number, string $message, ?string $imageUrl = null): void
    {
        if (!$number) {
            return;
        }
        $this->dispatchWhatsappJob($number, $message, $imageUrl);
    }

    public function testSendGroupMessage(string $message, ?string $imageUrl = null): void
    {
        $this->dispatchWhatsappJob($this->groupId, $message, $imageUrl);
    }

    // ===================================================================
    // FORMAT DETAIL PESAN (DIPERTAHANKAN SESUAI KODE ANDA)
    // ===================================================================

    protected function formatTripDetailsMessage(Trip $trip, string $statusMessage): string
    {
        $projectName = $trip->project_name ?? 'N/A';
        $driverName = $trip->user->name ?? 'N/A';
        $destination = data_get($trip->destination, 'address', 'N/A');
        $licensePlate = $trip->vehicle->license_plate ?? 'N/A';

        // Format tanggal ke zona waktu Indonesia
        $tglMuat = Carbon::parse($trip->created_at)->timezone('Asia/Jakarta')->format('d/m/Y');

        // Cek apakah notifikasi terkait proses bongkar
        $isBongkarProcess = in_array($statusMessage, [
            "Telah Tiba di Lokasi Bongkar",
            "Sedang dalam Proses Bongkar Barang",
            "Telah Selesai Bongkar Barang"
        ]);

        $tglBongkar = '-';
        if ($isBongkarProcess && $trip->updated_at) {
            $tglBongkar = Carbon::parse($trip->updated_at)->timezone('Asia/Jakarta')->format('d/m/Y');
        }

        $header = "MERALLIN TRANSLOG - " . now()->format('d/m/Y') . " - " . $projectName;

        return sprintf(
            "%s\n\n" .
                "DRIVER: %s\n" .
                "DESTINATION: %s\n" .
                "NOPOL: %s\n" .
                "TGL MUAT: %s\n" .
                "TGL BONGKAR: %s\n" .
                "STATUS: %s",
            $header,
            $driverName,
            $destination,
            $licensePlate,
            $tglMuat,
            $tglBongkar,
            strtoupper($statusMessage)
        );
    }

    // ===================================================================
    // METODE UNTUK SETIAP NOTIFIKASI (DIPERTAHANKAN & DIPERBAIKI)
    // ===================================================================

    private function dispatchNotification(Trip $trip, string $statusMessage, $photoPaths): void
    {
        if (empty($photoPaths)) {
            return;
        }

        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);

        // Pastikan $photoPaths selalu array
        $paths = is_array($photoPaths) ? $photoPaths : [$photoPaths];

        // Ambil semua path gambar dari struktur array yang mungkin nested
        $imageUrls = collect($paths)->flatten()->map(fn($path) => Storage::url($path))->filter()->values()->all();

        if (empty($imageUrls)) {
            return;
        }

        // Kirim gambar pertama dengan caption
        $firstImageUrl = array_shift($imageUrls);
        $this->dispatchWhatsappJob($this->groupId, $caption, $firstImageUrl, $trip->id);

        // Kirim sisa gambar tanpa caption
        foreach ($imageUrls as $url) {
            $this->dispatchWhatsappJob($this->groupId, '', $url, $trip->id);
            usleep(500000); // Jeda 0.5 detik antar dispatch
        }
    }

    public function notifyKedatanganMuat(Trip $trip): void
    {
        $this->dispatchNotification($trip, "Telah Tiba di Lokasi Muat", $trip->kedatangan_muat_photo_path);
    }

    public function notifyProsesMuat(Trip $trip): void
    {
        $this->dispatchNotification($trip, "Sedang dalam Proses Muat Barang", $trip->muat_photo_path);
    }

    public function notifySelesaiMuat(Trip $trip): void
    {
        // Mengambil hanya gambar terakhir dari semua proses muat
        $lastPhoto = collect($trip->muat_photo_path)->flatten()->last();
        $this->dispatchNotification($trip, "Telah Selesai Muat Barang", $lastPhoto);
    }

    public function notifyKedatanganBongkar(Trip $trip): void
    {
        $this->dispatchNotification($trip, "Telah Tiba di Lokasi Bongkar", $trip->kedatangan_bongkar_photo_path);
    }

    public function notifyProsesBongkar(Trip $trip): void
    {
        $this->dispatchNotification($trip, "Sedang dalam Proses Bongkar Barang", $trip->bongkar_photo_path);
    }

    public function notifySelesaiBongkar(Trip $trip): void
    {
        // Mengambil hanya gambar terakhir dari semua proses bongkar
        $lastPhoto = collect($trip->bongkar_photo_path)->flatten()->last();
        $this->dispatchNotification($trip, "Telah Selesai Bongkar Barang", $lastPhoto);
    }
}
