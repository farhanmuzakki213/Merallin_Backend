<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleLocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'trip_id',
        'keterangan',
        'start_location',
        'standby_photo_path',
        'standby_photo_status',
        'standby_photo_verified_by',
        'standby_photo_verified_at',
        'standby_photo_rejection_reason',
        'start_km_photo_path',
        'start_km_photo_status',
        'start_km_photo_verified_by',
        'start_km_photo_verified_at',
        'start_km_photo_rejection_reason',
        'end_km_photo_path',
        'end_km_photo_status',
        'end_km_photo_verified_by',
        'end_km_photo_verified_at',
        'end_km_photo_rejection_reason',
        'end_location',
        'status_vehicle_location',
        'status_lokasi',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'vehicle_id' => 'integer',
        'trip_id' => 'integer',
        'start_location' => 'array',
        'end_location' => 'array',
        'standby_photo_verified_at' => 'datetime',
        'start_km_photo_verified_at' => 'datetime',
        'end_km_photo_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'full_standby_photo_url',
        'full_start_km_photo_url',
        'full_end_km_photo_url',
        'start_location_map_url',
        'end_location_map_url',
    ];

    /**
     * Mengubah format created_at ke zona waktu WIB saat diakses.
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'),
        );
    }

    /**
     * Mengubah format updated_at ke zona waktu WIB saat diakses.
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'),
        );
    }

    /**
     * Menyesuaikan format tanggal saat model diubah menjadi array atau JSON.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::parse($date)
            ->setTimezone('Asia/Jakarta')
            ->toIso8601String();
    }

    // --- RELATIONS ---
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    // --- ACCESSORS for photo URLs ---
    protected function fullStandbyPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->standby_photo_path ? Storage::url($this->standby_photo_path) : null);
    }

    protected function fullStartKmPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->start_km_photo_path ? Storage::url($this->start_km_photo_path) : null);
    }

    protected function fullEndKmPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->end_km_photo_path ? Storage::url($this->end_km_photo_path) : null);
    }

    /**
     * Accessor untuk mendapatkan URL Google Maps dari lokasi awal.
     */
    protected function startLocationMapUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $location = $this->start_location;
                if (!empty($location['latitude']) && !empty($location['longitude'])) {
                    return "https://www.google.com/maps/search/?api=1&query={$location['latitude']},{$location['longitude']}";
                }
                return null;
            }
        );
    }

    /**
     * Accessor untuk mendapatkan URL Google Maps dari lokasi akhir.
     */
    protected function endLocationMapUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $location = $this->end_location;
                if (!empty($location['latitude']) && !empty($location['longitude'])) {
                    return "https://www.google.com/maps/search/?api=1&query={$location['latitude']},{$location['longitude']}";
                }
                return null;
            }
        );
    }
}
