<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppNotificationService;

class TestWhatsApp extends Command
{
    /**
     * Signature command, sekarang menerima opsi --gambar.
     * @var string
     */
    protected $signature = 'wa:test {--nomor=} {--pesan=} {--gambar=}';

    /**
     * @var string
     */
    protected $description = 'Mengirim pesan tes ke nomor atau grup WhatsApp, bisa dengan gambar.';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppNotificationService $waNotificationService)
    {
        $nomor = $this->option('nomor');
        $pesan = $this->option('pesan') ?: "👋 Halo! Ini adalah pesan tes dari server Merallin.";
        $gambarUrl = $this->option('gambar');

        if ($gambarUrl) {
            $this->info("Melampirkan gambar dari: " . $gambarUrl);
        }

        if ($nomor) {
            $this->info("Mengirim pesan tes ke nomor: {$nomor}...");
            $waNotificationService->testSendPersonalMessage($nomor, $pesan, $gambarUrl);
        } else {
            $this->info('Mengirim pesan tes ke grup default...');
            $waNotificationService->testSendGroupMessage($pesan, $gambarUrl);
        }

        $this->info('✅ Job untuk mengirim pesan tes berhasil ditambahkan ke antrian!');
        $this->comment('Jalankan "php artisan queue:work" untuk memprosesnya.');

        return 0;
    }
}
