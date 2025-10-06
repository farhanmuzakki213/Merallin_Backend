<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Trip;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WhatsAppNotificationService
{
    protected $fonnteToken;
    protected $groupId;

    public function __construct()
    {
        $this->fonnteToken = config('services.whatsapp.fonnte_token');
        $this->groupId = config('services.whatsapp.group_id');
    }

    /**
     * Mengirim pesan dengan gambar via API Fonnte.
     * Fonnte akan mengirim gambar satu per satu.
     * Teks caption hanya akan dikirim bersama gambar pertama.
     *
     * @param string $target
     * @param string $caption
     * @param array $imageUrls
     * @return bool
     */
    protected function sendMessageWithImages(string $target, string $caption, array $imageUrls): bool
    {
        Log::info('--- Memulai sendMessageWithImages ---');
        if (!$this->fonnteToken) {
            Log::error('[DEBUG] Fonnte token tidak dikonfigurasi.');
            return false;
        }

        // Jika tidak ada gambar, kirim sebagai teks biasa
        if (empty($imageUrls)) {
            Log::info('[DEBUG] Tidak ada gambar, mengirim sebagai teks biasa.');
            return $this->sendTextMessage($target, $caption);
        }

        // Kirim gambar pertama dengan caption
        $firstImageUrl = array_shift($imageUrls);

        Log::info('[DEBUG] Payload untuk gambar pertama:', [
            'target' => $target,
            'caption_length' => strlen($caption),
            'url' => $firstImageUrl
        ]);
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => $this->fonnteToken])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $caption,
                    'url'     => $firstImageUrl,
                ]);
            Log::info('[DEBUG] Respon Fonnte untuk gambar pertama:', ['status' => $response->status(), 'body' => $response->json()]);
            if ($response->failed()) {
                Log::error('[DEBUG] Fonnte mengembalikan error.', ['status' => $response->status(), 'body' => $response->json()]);
                return false;
            }
            sleep(2);

            foreach ($imageUrls as $imageUrl) {
                Log::info('[DEBUG] Mengirim gambar tambahan:', ['url' => $imageUrl]);
                $response = Http::timeout(30)
                    ->withHeaders(['Authorization' => $this->fonnteToken])
                    ->post('https://api.fonnte.com/send', [
                        'target'  => $target,
                        'url'     => $imageUrl,
                    ]);
                Log::info('[DEBUG] Respon Fonnte untuk gambar tambahan:', ['status' => $response->status(), 'body' => $response->json()]);
                sleep(2);
            }

            Log::info('--- sendMessageWithImages Selesai ---');
            return true;
        } catch (\Exception $e) {
            Log::error('[DEBUG] Exception saat mengirim pesan via Fonnte: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper untuk mengirim pesan teks saja (fungsi lama sendMessage).
     */
    protected function sendTextMessage(string $target, string $message): bool
    {
        if (!$this->fonnteToken) {
            Log::error('Fonnte token is not configured.');
            return false;
        }
        try {
            Http::timeout(30)
                ->withHeaders(['Authorization' => $this->fonnteToken])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp text message via Fonnte: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper untuk mengirim pesan dengan gambar ke grup default.
     */
    public function sendGroupMessageWithImages(string $caption, array $imageUrls): bool
    {
        if (!$this->groupId) {
            Log::error('WhatsApp Group ID is not configured.');
            return false;
        }
        return $this->sendMessageWithImages($this->groupId, $caption, $imageUrls);
    }

    /**
     * Memformat pesan notifikasi sesuai template baru.
     */
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
        if ($isBongkarProcess) {
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

    // --- METODE UNTUK SETIAP NOTIFIKASI ---

    public function notifyKedatanganMuat(Trip $trip): bool
    {
        $debugUrlConfig = config('filesystems.disks.public.url');
        Log::info('[DEBUG ULTIMATE TEST] Nilai config url saat ini: ' . $debugUrlConfig);
        if (empty($trip->kedatangan_muat_photo_path)) {
            return false; // Berhenti jika gambar tidak ada
        }
        $statusMessage = "Telah Tiba di Lokasi Muat";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $imageUrls = [asset(Storage::url($trip->kedatangan_muat_photo_path))];
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }

    public function notifyProsesMuat(Trip $trip): bool
    {
        if (empty($trip->muat_photo_path) || !is_array($trip->muat_photo_path)) {
            return false; // Berhenti jika array gambar kosong
        }
        $statusMessage = "Sedang dalam Proses Muat Barang";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $imageUrls = [];
        foreach ($trip->muat_photo_path as $gudangPhotos) {
            foreach ($gudangPhotos as $path) {
                $imageUrls[] = Storage::url($path);
            }
        }
        if (empty($imageUrls)) return false; // Pastikan lagi setelah loop
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }

    public function notifySelesaiMuat(Trip $trip): bool
    {
        if (empty($trip->muat_photo_path) || !is_array($trip->muat_photo_path)) {
            return false;
        }
        $statusMessage = "Telah Selesai Muat Barang";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $allPaths = array_merge(...array_values($trip->muat_photo_path));
        if (empty($allPaths)) {
            return false;
        }
        $imageUrls = [Storage::url(last($allPaths))];
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }

    public function notifyKedatanganBongkar(Trip $trip): bool
    {
        if (empty($trip->kedatangan_bongkar_photo_path)) {
            return false;
        }
        $statusMessage = "Telah Tiba di Lokasi Bongkar";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $imageUrls = [Storage::url($trip->kedatangan_bongkar_photo_path)];
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }

    public function notifyProsesBongkar(Trip $trip): bool
    {
        if (empty($trip->bongkar_photo_path) || !is_array($trip->bongkar_photo_path)) {
            return false;
        }
        $statusMessage = "Sedang dalam Proses Bongkar Barang";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $imageUrls = [];
        foreach ($trip->bongkar_photo_path as $gudangPhotos) {
            foreach ($gudangPhotos as $path) {
                $imageUrls[] = Storage::url($path);
            }
        }
        if (empty($imageUrls)) return false;
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }

    public function notifySelesaiBongkar(Trip $trip): bool
    {
        if (empty($trip->bongkar_photo_path) || !is_array($trip->bongkar_photo_path)) {
            return false;
        }
        $statusMessage = "Telah Selesai Bongkar Barang";
        $caption = $this->formatTripDetailsMessage($trip, $statusMessage);
        $allPaths = array_merge(...array_values($trip->bongkar_photo_path));
        if (empty($allPaths)) {
            return false;
        }
        $imageUrls = [Storage::url(last($allPaths))];
        return $this->sendGroupMessageWithImages($caption, $imageUrls);
    }
}
