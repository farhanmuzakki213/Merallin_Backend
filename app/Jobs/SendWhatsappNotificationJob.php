<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\WhatsappMessageFailed;
use Throwable;

class SendWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5; // Jumlah percobaan ulang
    public $timeout = 120; // Waktu timeout job
    protected $payload;
    protected $contextId; // Bisa berupa trip_id atau null

    public function __construct(array $payload, $contextId = null)
    {
        $this->payload = $payload;
        $this->contextId = $contextId;
    }

    public function handle(): void
    {
        $waServerUrl = config('services.whatsapp.server_url');
        $waSecretKey = config('services.whatsapp.secret_key');

        if (!$waSecretKey || !$waServerUrl) {
            $this->fail(new \Exception("URL atau Secret Key WA Server tidak diatur."));
            return;
        }

        Log::info("Mencoba mengirim WA (Percobaan ke-{$this->attempts()})", $this->payload);

        $response = Http::timeout(45)
                        ->withToken($waSecretKey)
                        ->post("{$waServerUrl}/send", $this->payload);

        if ($response->failed()) {
            throw new \Exception("Gagal menghubungi WA Server. Status: " . $response->status() . " - " . $response->body());
        }

        Log::info("Pesan WA berhasil dikirim.", ['response' => $response->json()]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Pesan WA GAGAL TOTAL setelah {$this->tries} percobaan.", [
            'payload' => $this->payload,
            'context_id' => $this->contextId,
            'error' => $exception->getMessage()
        ]);

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();
        $notification = new WhatsappMessageFailed($this->payload, $this->contextId, $exception->getMessage());

        foreach ($admins as $admin) {
            $admin->notify($notification);
        }
    }
}
