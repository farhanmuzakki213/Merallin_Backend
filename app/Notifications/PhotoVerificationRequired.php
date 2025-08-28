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
    protected $title; // <-- TAMBAHKAN INI

    public function __construct(Trip $trip, string $message, string $title = 'Verifikasi Foto Diperlukan') // <-- TAMBAHKAN DEFAULT TITLE
    {
        $this->trip = $trip;
        $this->message = $message;
        $this->title = $title; // <-- INISIALISASI
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        $notifiable->load('pushSubscriptions');
        if ($notifiable->pushSubscriptions->isNotEmpty()) {
            $channels[] = WebPushChannel::class;
        }
        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => $this->title,     // <-- TAMBAHKAN TITLE
            'message' => $this->message,
            'trip_id' => $this->trip->id,
            'url'     => route('trips.table'),
            'type'    => 'trip_photo', // <-- TAMBAHKAN TIPE UNTUK ICON
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('trips.table');

        Log::info('Mempersiapkan Web Push Notification:', [
            'message' => $this->message,
            'url' => $url,
            'penerima_id' => $notifiable->id
        ]);

        return (new WebPushMessage)
            ->title($this->title) // Menggunakan title dari konstruktor
            ->body($this->message)
            ->image(asset('storage/app/public/trip_photos/' . $this->trip->photo_filename)) // Contoh: Tambahkan gambar dari trip jika ada
            ->icon(asset('favicon.png')) // Icon kecil di notifikasi
            ->badge(asset('badge.png')) // Badge untuk Android
            ->action('Lihat Detail', 'view_trip') // Tombol aksi
            ->data(['url' => $url, 'notification_id' => $notification->id, 'trip_id' => $this->trip->id]);
    }
}
