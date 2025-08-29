<?php

namespace App\Notifications;

use App\Models\VehicleLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class VehicleLocationPhotoVerificationRequired extends Notification
{
    use Queueable;

    public function __construct(
        protected VehicleLocation $location,
        protected string $photoDisplayName,
        protected string $vehicleInfo,
        protected string $publicPhotoUrl,
        protected string $title = "Verifikasi Foto Lokasi Diperlukan"
    ) {}

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => "Foto '{$this->photoDisplayName}' dari {$this->location->user->name} untuk kendaraan {$this->vehicleInfo} perlu verifikasi.",
            'location_id' => $this->location->id,
            'url' => route('vehicleLocations.table'),
            'type' => 'vehicle_location_verification',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body("Foto '{$this->photoDisplayName}' dari {$this->location->user->name} untuk {$this->vehicleInfo} perlu verifikasi.")
            ->image($this->publicPhotoUrl)
            ->icon(asset('images/logo/auth-logo128.svg'))
            ->tag('verification-location-' . $this->location->id . '-' . $this->photoDisplayName)
            ->data(['url' => route('vehicleLocations.table'), 'notification_id' => $notification->id]);
    }
}
