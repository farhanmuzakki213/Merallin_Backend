<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Carbon\Carbon;

class PhotoVerificationRequired extends Notification
{
    use Queueable;

    protected $trip;
    protected $photoDisplayName;
    protected $projectName;
    protected $publicPhotoUrl;
    protected $title;
    protected $bodyMessage;

    public function __construct(Trip $trip, string $photoDisplayName, string $projectName, string $publicPhotoUrl, string $title = "Verifikasi Foto Trip Diperlukan")
    {
        $this->trip = $trip;
        $this->photoDisplayName = $photoDisplayName;
        $this->projectName = $projectName;
        $this->publicPhotoUrl = $publicPhotoUrl;
        $this->title = $title;

        // Pesan yang lebih spesifik dan tidak membingungkan
        $this->bodyMessage = "Foto '{$this->photoDisplayName}' untuk proyek '{$this->projectName}' oleh driver {$this->trip->user->name} memerlukan verifikasi Anda.";
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }
        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'        => $this->title,
            'message'      => $this->bodyMessage,
            'trip_id'      => $this->trip->id,
            'project_name' => $this->projectName,
            'url'          => route('trips.table'),
            'type'         => 'trip_photo_verification',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('trips.table');

        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->bodyMessage)
            ->image($this->publicPhotoUrl) // [FIXED] Menggunakan URL dinamis yang dikirim
            ->icon(asset('images/logo/auth-logo128.svg')) // Menggunakan logo aplikasi
            ->badge(asset('images/logo/auth-logo128.svg'))
            ->tag('verification-' . $this->trip->id . '-' . $this->photoDisplayName) // Tag unik
            ->data(['url' => $url, 'notification_id' => $notification->id]);
    }
}
