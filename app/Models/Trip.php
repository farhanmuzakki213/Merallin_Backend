<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_name',
        'origin',
        'destination',
        'license_plate',
        'start_km',
        'start_km_photo_path',
        'muat_photo_path',
        'bongkar_photo_path',
        'end_km_photo_path',
        'end_km',
        'delivery_letter_path',
        'status_lokasi',
        'status_muatan',
        'status_trip',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_start_km_photo_url',
        'full_muat_photo_url',
        'full_bongkar_photo_url',
        'full_end_km_photo_url',
        'full_delivery_letter_urls',
    ];

    /**
     * Mendefinisikan relasi ke User (Driver).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // --- ACCESSORS UNTUK URL FOTO ---

    /**
     * Accessor untuk mendapatkan URL lengkap foto kilometer awal.
     */
    protected function fullStartKmPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_km_photo_path ? Storage::url($this->start_km_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto muat.
     */
    protected function fullMuatPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->muat_photo_path ? Storage::url($this->muat_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto bongkar.
     */
    protected function fullBongkarPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->bongkar_photo_path ? Storage::url($this->bongkar_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto kilometer akhir.
     */
    protected function fullEndKmPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_km_photo_path ? Storage::url($this->end_km_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap surat jalan.
     */
    protected function fullDeliveryLetterUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->delivery_letter_path ? Storage::url($this->delivery_letter_path) : null,
        );
    }

    protected function fullDeliveryLetterUrls(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->delivery_letter_path) {
                    return [];
                }
                return array_map(function ($path) {
                    return Storage::url($path);
                }, $this->delivery_letter_path);
            }
        );
    }
}
