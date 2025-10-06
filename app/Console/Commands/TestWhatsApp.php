<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppNotificationService;

class TestWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:test {--nomor=} {--pesan=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim pesan tes ke nomor atau grup WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppNotificationService $waNotificationService)
    {
        $nomor = $this->option('nomor');
        $pesan = $this->option('pesan') ?: "👋 Halo! Ini adalah pesan tes dari server Merallin. Bot notifikasi berhasil terhubung.";

        if ($nomor) {
            // Jika ada input nomor, kirim ke nomor tersebut
            $this->info("Mengirim pesan tes ke nomor: {$nomor}...");
            $isSuccess = $waNotificationService->sendPersonalMessage($nomor, $pesan);
        } else {
            // Jika tidak ada, kirim ke grup default
            $this->info('Mengirim pesan tes ke grup default...');
            $isSuccess = $waNotificationService->sendGroupMessage($pesan);
        }

        if ($isSuccess) {
            $this->info('✅ Pesan tes berhasil dikirim!');
        } else {
            $this->error('❌ Gagal mengirim pesan. Cek log di storage/logs/laravel.log dan terminal wa-server.');
        }

        return 0;
    }
}
