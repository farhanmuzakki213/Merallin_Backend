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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'project_name',
        'origin',
        'destination',
        'license_plate',
        'start_km',
        'start_km_photo_path',
        'start_km_photo_status',
        'start_km_photo_verified_by',
        'start_km_photo_verified_at',
        'start_km_photo_rejection_reason',
        'muat_photo_path',
        'muat_photo_status',
        'muat_photo_verified_by',
        'muat_photo_verified_at',
        'muat_photo_rejection_reason',
        'bongkar_photo_path',
        'bongkar_photo_status',
        'bongkar_photo_verified_by',
        'bongkar_photo_verified_at',
        'bongkar_photo_rejection_reason',
        'end_km',
        'end_km_photo_path',
        'end_km_photo_status',
        'end_km_photo_verified_by',
        'end_km_photo_verified_at',
        'end_km_photo_rejection_reason',
        'delivery_letter_path',
        'delivery_letter_initial_status',
        'delivery_letter_initial_verified_by',
        'delivery_letter_initial_verified_at',
        'delivery_letter_initial_rejection_reason',
        'delivery_letter_final_status',
        'delivery_letter_final_verified_by',
        'delivery_letter_final_verified_at',
        'delivery_letter_final_rejection_reason',
        'delivery_order_path',
        'delivery_order_status',
        'delivery_order_verified_by',
        'delivery_order_verified_at',
        'delivery_order_rejection_reason',
        'timbangan_kendaraan_photo_path',
        'timbangan_kendaraan_photo_status',
        'timbangan_kendaraan_photo_verified_by',
        'timbangan_kendaraan_photo_verified_at',
        'timbangan_kendaraan_photo_rejection_reason',
        'segel_photo_path',
        'segel_photo_status',
        'segel_photo_verified_by',
        'segel_photo_verified_at',
        'segel_photo_rejection_reason',
        'status_trip',
        'jenis_trip',
        'status_lokasi',
        'status_muatan',
    ];

    /**
     * The attributes that should be guarded.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'delivery_letter_path' => 'array',
        'bongkar_photo_path'   => 'array',
        'start_km_photo_verified_at' => 'datetime',
        'muat_photo_verified_at' => 'datetime',
        'bongkar_photo_verified_at' => 'datetime',
        'end_km_photo_verified_at' => 'datetime',
        'delivery_letter_initial_verified_at' => 'datetime',
        'delivery_letter_final_verified_at' => 'datetime',
        'delivery_order_verified_at' => 'datetime',
        'timbangan_kendaraan_photo_verified_at' => 'datetime',
        'segel_photo_verified_at' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi ke User (Admin yang memverifikasi).
     */
    public function verifiedByStartKm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'start_km_photo_verified_by');
    }

    public function verifiedByMuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muat_photo_verified_by');
    }

    public function verifiedByDeliveryOrder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_order_verified_by');
    }

    public function verifiedByTimbanganKendaraan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'timbangan_kendaraan_photo_verified_by');
    }

    public function verifiedBySegel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'segel_photo_verified_by');
    }

    public function verifiedByBongkar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bongkar_photo_verified_by');
    }

    public function verifiedByEndKm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'end_km_photo_verified_by');
    }

    public function verifiedByDeliveryLetterInitial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_letter_initial_verified_by');
    }

    public function verifiedByDeliveryLetterFinal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_letter_final_verified_by');
    }

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
            get: fn() => $this->start_km_photo_path ? Storage::url($this->start_km_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto muat.
     */
    protected function fullMuatPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->muat_photo_path ? Storage::url($this->muat_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap dari foto bongkar yang berupa array.
     *
     * @return array
     */
    public function getFullBongkarPhotoUrlAttribute(): array
    {
        $paths = $this->bongkar_photo_path;

        // Periksa apakah $paths tidak kosong dan merupakan sebuah array
        if (!empty($paths) && is_array($paths)) {
            // Gunakan array_map untuk menerapkan Storage::url() pada setiap elemen path
            return array_map(function ($path) {
                return Storage::url($path);
            }, $paths);
        }

        // Kembalikan array kosong jika tidak ada foto
        return [];
    }


    /**
     * Accessor untuk mendapatkan URL lengkap foto kilometer akhir.
     */
    protected function fullEndKmPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->end_km_photo_path ? Storage::url($this->end_km_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap surat jalan.
     */

    protected function fullDeliveryLetterUrls(): Attribute
    {
        return Attribute::make(
            get: function () {
                $deliveryPaths = $this->delivery_letter_path; // Ini adalah array dari $casts

                if (empty($deliveryPaths)) {
                    return [];
                }

                // 1. Gabungkan semua path menjadi satu array datar
                $allPaths = [];
                if (!empty($deliveryPaths['initial_letters'])) {
                    $allPaths = array_merge($allPaths, $deliveryPaths['initial_letters']);
                }
                if (!empty($deliveryPaths['final_letters'])) {
                    $allPaths = array_merge($allPaths, $deliveryPaths['final_letters']);
                }

                if (empty($allPaths)) {
                    return [];
                }

                // 2. Sekarang proses array yang sudah datar dengan array_map
                return array_map(function ($path) {
                    // Pastikan $path adalah string sebelum diproses
                    if (is_string($path)) {
                        return Storage::url($path);
                    }
                    return null;
                }, $allPaths);
            }
        );
    }
}
