<?php

namespace App\Notifications;

use App\Models\BbmKendaraan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BbmPhotoVerificationRequired extends Notification
{
    use Queueable;

    protected $bbmKendaraan;
    protected $photoDisplayName;
    protected $vehicleInfo;
    protected $publicPhotoUrl;
    protected $title;
    protected $bodyMessage;

    public function __construct(BbmKendaraan $bbmKendaraan, string $photoDisplayName, string $vehicleInfo, string $publicPhotoUrl, string $title = "Verifikasi Foto BBM Diperlukan")
    {
        $this->bbmKendaraan = $bbmKendaraan;
        $this->photoDisplayName = $photoDisplayName;
        $this->vehicleInfo = $vehicleInfo;
        $this->publicPhotoUrl = $publicPhotoUrl;
        $this->title = $title;
        $this->bodyMessage = "Foto '{$this->photoDisplayName}' untuk kendaraan '{$this->vehicleInfo}' oleh driver {$this->bbmKendaraan->user->name} memerlukan verifikasi Anda.";
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
            'bbm_id'       => $this->bbmKendaraan->id,
            'vehicle_info' => $this->vehicleInfo,
            'url'          => route('bbm.table'),
            'type'         => 'bbm_photo_verification',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('bbm.table'); // Ganti dengan route yang akan kita buat

        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->bodyMessage)
            ->image($this->publicPhotoUrl)
            ->icon(asset('images/logo/auth-logo128.svg'))
            ->badge(asset('images/logo/auth-logo128.svg'))
            ->tag('verification-bbm-' . $this->bbmKendaraan->id . '-' . $this->photoDisplayName)
            ->data(['url' => $url, 'notification_id' => $notification->id]);
    }
}
