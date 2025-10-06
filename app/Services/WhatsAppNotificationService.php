<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Trip;
use Illuminate\Support\Facades\Log;

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
     * Mengirim pesan via API Fonnte.
     *
     * @param string $target (Nomor personal atau Group ID)
     * @param string $message
     * @return bool
     */
    protected function sendMessage(string $target, string $message): bool
    {
        if (!$this->fonnteToken) {
            Log::error('Fonnte token is not configured.');
            return false;
        }

        try {
            // Fonnte menggunakan endpoint 'https://api.fonnte.com/send'
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => $this->fonnteToken]) // Otentikasi menggunakan token
                ->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

            if ($response->failed()) {
                Log::error('Failed to send WhatsApp message via Fonnte', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

            // Anda bisa menambahkan logika untuk memeriksa response success dari Fonnte jika perlu
            // Log::info('Fonnte response: ', $response->json());

            return true;
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp message via Fonnte: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper untuk mengirim pesan tes ke nomor personal.
     * Nomor harus dalam format 628...
     *
     * @param string $phoneNumber (e.g., "62812...")
     * @param string $message
     * @return bool
     */
    public function sendPersonalMessage(string $phoneNumber, string $message): bool
    {
        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Helper untuk mengirim pesan ke grup default dari config.
     *
     * @param string $message
     * @return bool
     */
    public function sendGroupMessage(string $message): bool
    {
        if (!$this->groupId) {
            Log::error('WhatsApp Group ID is not configured.');
            return false;
        }
        return $this->sendMessage($this->groupId, $message);
    }

    /**
     * Memformat pesan notifikasi standar.
     */
    protected function formatMessage(Trip $trip, string $statusMessage): string
    {
        // Fonnte mendukung styling text (bold, italic, dll)
        // Kita gunakan *...* untuk bold
        return sprintf(
            "🔔 *Notifikasi Status Perjalanan*\n\n" .
            "*ID Pesanan:* %s\n" .
            "*Driver:* %s\n" .
            "*Kendaraan:* %s (%s)\n" .
            "*Status:* %s\n\n" .
            "Terima kasih.",
            $trip->order_id ?? 'N/A',
            $trip->user->name ?? 'N/A',
            $trip->vehicle->type ?? 'N/A',
            $trip->vehicle->license_plate ?? 'N/A',
            $statusMessage
        );
    }

    // --- METODE UNTUK SETIAP NOTIFIKASI (TIDAK PERLU DIUBAH) ---

    public function notifyKedatanganMuat(Trip $trip)
    {
        $message = $this->formatMessage($trip, "✅ Telah Tiba di Lokasi Muat");
        $this->sendGroupMessage($message);
    }

    public function notifyProsesMuat(Trip $trip)
    {
        $message = $this->formatMessage($trip, "⏳ Sedang dalam Proses Muat Barang");
        $this->sendGroupMessage($message);
    }

    public function notifySelesaiMuat(Trip $trip)
    {
        $message = $this->formatMessage($trip, "✅ Telah Selesai Muat Barang");
        $this->sendGroupMessage($message);
    }

    public function notifyKedatanganBongkar(Trip $trip)
    {
        $message = $this->formatMessage($trip, "✅ Telah Tiba di Lokasi Bongkar");
        $this->sendGroupMessage($message);
    }

    public function notifyProsesBongkar(Trip $trip)
    {
        $message = $this->formatMessage($trip, "⏳ Sedang dalam Proses Bongkar Barang");
        $this->sendGroupMessage($message);
    }

    public function notifySelesaiBongkar(Trip $trip)
    {
        $message = $this->formatMessage($trip, "✅ Telah Selesai Bongkar Barang");
        $this->sendGroupMessage($message);
    }
}
