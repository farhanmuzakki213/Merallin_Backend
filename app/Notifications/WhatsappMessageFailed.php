<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WhatsappMessageFailed extends Notification
{
    use Queueable;

    protected $payload;
    protected $contextId;
    protected $errorMessage;

    public function __construct(array $payload, $contextId, string $errorMessage)
    {
        $this->payload = $payload;
        $this->contextId = $contextId;
        $this->errorMessage = $errorMessage;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Gagal Mengirim Notifikasi WhatsApp',
            'message' => "Pesan WA ke nomor {$this->payload['to']} gagal terkirim.",
            'context_id' => $this->contextId,
            'reason' => $this->errorMessage,
            'payload' => $this->payload
        ];
    }
}
