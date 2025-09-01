<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BbmKendaraan extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel secara eksplisit.
     */
    protected $table = 'bbm_kendaraan';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'status_bbm_kendaraan',
        'status_pengisian',
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
        'nota_pengisian_photo_path',
        'nota_pengisian_photo_status',
        'nota_pengisian_photo_verified_by',
        'nota_pengisian_photo_verified_at',
        'nota_pengisian_photo_rejection_reason',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     */
    protected $casts = [
        'start_km_photo_verified_at' => 'datetime',
        'end_km_photo_verified_at' => 'datetime',
        'nota_pengisian_photo_verified_at' => 'datetime',
    ];

    /**
     * Accessor yang ditambahkan ke representasi array model.
     * @var array
     */
    protected $appends = [
        'full_start_km_photo_url',
        'full_end_km_photo_url',
        'full_nota_pengisian_photo_url',
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
     * Mendapatkan user yang membuat entri ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan kendaraan yang terkait dengan entri ini.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Mendapatkan user yang memverifikasi foto kilometer awal.
     */
    public function startKmVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'start_km_photo_verified_by');
    }

    /**
     * Mendapatkan user yang memverifikasi foto kilometer akhir.
     */
    public function endKmVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'end_km_photo_verified_by');
    }

    /**
     * Mendapatkan user yang memverifikasi foto nota pengisian.
     */
    public function notaVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nota_pengisian_photo_verified_by');
    }

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
     * Accessor untuk mendapatkan URL lengkap foto kilometer akhir.
     */
    protected function fullEndKmPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->end_km_photo_path ? Storage::url($this->end_km_photo_path) : null,
        );
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto nota pengisian.
     */
    protected function fullNotaPengisianPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->nota_pengisian_photo_path ? Storage::url($this->nota_pengisian_photo_path) : null,
        );
    }
}
