<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
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
        'slot_time',
        'jenis_berat',
        'vehicle_id',
        'start_km',
        'start_km_photo_path',
        'start_km_photo_status',
        'start_km_photo_verified_by',
        'start_km_photo_verified_at',
        'start_km_photo_rejection_reason',
        'km_muat_photo_path',
        'km_muat_photo_status',
        'km_muat_photo_verified_by',
        'km_muat_photo_verified_at',
        'km_muat_photo_rejection_reason',
        'kedatangan_muat_photo_path',
        'kedatangan_muat_photo_status',
        'kedatangan_muat_photo_verified_by',
        'kedatangan_muat_photo_verified_at',
        'kedatangan_muat_photo_rejection_reason',
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
        'kedatangan_bongkar_photo_path',
        'kedatangan_bongkar_photo_status',
        'kedatangan_bongkar_photo_verified_by',
        'kedatangan_bongkar_photo_verified_at',
        'kedatangan_bongkar_photo_rejection_reason',
        'delivery_letter_path',
        'delivery_letter_initial_status',
        'delivery_letter_initial_verified_by',
        'delivery_letter_initial_verified_at',
        'delivery_letter_initial_rejection_reason',
        'delivery_letter_final_status',
        'delivery_letter_final_verified_by',
        'delivery_letter_final_verified_at',
        'delivery_letter_final_rejection_reason',
        'delivery_order_photo_path',
        'delivery_order_photo_status',
        'delivery_order_photo_verified_by',
        'delivery_order_photo_verified_at',
        'delivery_order_photo_rejection_reason',
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
        'user_id' => 'integer',
        'vehicle_id' => 'integer',
        'start_km' => 'integer',
        'end_km' => 'integer',
        'origin' => 'array',
        'destination' => 'array',
        'delivery_letter_path' => 'array',
        'bongkar_photo_path'   => 'array',
        'muat_photo_path'      => 'array',
        'start_km_photo_verified_at' => 'datetime',
        'km_muat_photo_verified_at' => 'datetime',
        'kedatangan_muat_photo_verified_at' => 'datetime',
        'delivery_order_photo_verified_at' => 'datetime',
        'muat_photo_verified_at' => 'datetime',
        'delivery_letter_initial_verified_at' => 'datetime',
        'timbangan_kendaraan_photo_verified_at' => 'datetime',
        'segel_photo_verified_at' => 'datetime',
        'end_km_photo_verified_at' => 'datetime',
        'kedatangan_bongkar_photo_verified_at' => 'datetime',
        'bongkar_photo_verified_at' => 'datetime',
        'delivery_letter_final_verified_at' => 'datetime',
    ];

    /**
     * Mendefinisikan relasi ke User (Admin yang memverifikasi).
     */
    public function verifiedByStartKm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'start_km_photo_verified_by');
    }

    public function verifiedByKmMuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'km_muat_photo_verified_by');
    }

    public function verifiedByKedatanganMuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kedatangan_muat_photo_verified_by');
    }

    public function verifiedByKedatanganBongkar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kedatangan_bongkar_photo_verified_by');
    }

    public function verifiedByMuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muat_photo_verified_by');
    }

    public function verifiedByDeliveryOrder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_order_photo_verified_by');
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
        'full_km_muat_photo_url',
        'full_kedatangan_muat_photo_url',
        'full_delivery_order_photo_url',
        'full_muat_photo_urls',
        'full_timbangan_kendaraan_photo_url',
        'full_segel_photo_url',
        'full_end_km_photo_url',
        'full_kedatangan_bongkar_photo_url',
        'full_bongkar_photo_urls',
        'full_delivery_letter_urls',
    ];

    /**
     * Mendefinisikan relasi ke User (Driver).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FUNGSI BARU: Mendefinisikan relasi ke Vehicle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // --- ACCESSORS UNTUK URL FOTO ---

    /**
     * Accessor untuk mendapatkan URL lengkap foto kilometer awal.
     */
    protected function generateSingleUrl(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }

    protected function generateMultipleUrls(?array $paths): array
    {
        if (empty($paths)) {
            return [];
        }
        return array_map(fn($path) => Storage::url($path), $paths);
    }

    protected function fullStartKmPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->start_km_photo_path));
    }

    protected function fullKmMuatPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->km_muat_photo_path));
    }

    protected function fullKedatanganMuatPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->kedatangan_muat_photo_path));
    }

    protected function fullDeliveryOrderPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->delivery_order_photo_path));
    }

    protected function fullMuatPhotoUrls(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateMultipleUrls($this->muat_photo_path));
    }

    protected function fullTimbanganKendaraanPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->timbangan_kendaraan_photo_path));
    }

    protected function fullSegelPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->segel_photo_path));
    }

    protected function fullEndKmPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->end_km_photo_path));
    }

    protected function fullKedatanganBongkarPhotoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateSingleUrl($this->kedatangan_bongkar_photo_path));
    }

    protected function fullBongkarPhotoUrls(): Attribute
    {
        return Attribute::make(get: fn() => $this->generateMultipleUrls($this->bongkar_photo_path));
    }

    protected function fullDeliveryLetterUrls(): Attribute
    {
        return Attribute::make(get: function () {
            $paths = $this->delivery_letter_path ?? [];
            return [
                'initial' => $this->generateMultipleUrls($paths['initial_letters'] ?? []),
                'final' => $this->generateMultipleUrls($paths['final_letters'] ?? []),
            ];
        });
    }

    public function vehicleLocations()
    {
        return $this->hasMany(VehicleLocation::class);
    }
}
