<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Support\Facades\Log;

class PhotoVerificationRequired extends Notification
{
    use Queueable;

    protected $trip;
    protected $message;

    public function __construct(Trip $trip, string $message)
    {
        $this->trip = $trip;
        $this->message = $message;
        // dd($this->message, $this->trip);
    }

    public function via($notifiable): array
    {
        $notifiable->load('pushSubscriptions');
        if ($notifiable->pushSubscriptions->isNotEmpty()) {
            return [WebPushChannel::class];
        }
        return [];
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('trips.table');

        Log::info('Mempersiapkan Web Push Notification:', [
            'message' => $this->message,
            'url' => $url,
            'penerima_id' => $notifiable->id
        ]);

        // HAPUS BARIS INI:
        // dd($this->message, $url, $notifiable->toArray());

        return (new WebPushMessage)
            ->title('Verifikasi Foto Diperlukan')
            // ->icon('/icon.png') // Anda bisa uncomment ini jika punya file icon di public/icon.png
            ->body($this->message)
            ->action('Lihat Detail', 'view_trip') // 'view_trip' adalah tag aksi
            ->data(['url' => $url]);
    }
}
