<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Lembur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemburs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'jenis_hari',
        'department',
        'tanggal_lembur',
        'keterangan_lembur',
        'mulai_jam_lembur',
        'selesai_jam_lembur',
        'status_lembur',
        'persetujuan_direksi',
        'alasan',
        'uuid',
        'file_path',

        'jam_mulai_aktual',
        'foto_mulai_path',
        'lokasi_mulai',
        'jam_selesai_aktual',
        'foto_selesai_path',
        'lokasi_selesai',

        'total_jam',
        'gaji_lembur',
    ];

    protected $appends = [
        'file_url',
        'file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'lokasi_mulai' => 'array',
        'lokasi_selesai' => 'array',
    ];

    /**
     * Menggunakan event 'creating' untuk memastikan setiap data lembur baru
     * akan memiliki UUID yang unik secara otomatis sebelum disimpan ke database.
     */
    protected static function booted(): void
    {
        static::creating(function ($lembur) {
            if (empty($lembur->uuid)) {
                $lembur->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Menghasilkan URL ke route 'lembur.share' dengan parameter UUID,
     * yang merupakan URL berbagi yang aman untuk file yang sudah ditandatangani.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->uuid ? route('lembur.share', $this->uuid) : null;
    }

    /**
     * Mendapatkan URL yang dapat diakses publik untuk file yang disimpan.
     */
    public function getFileAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }


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
     * Mendapatkan user yang memiliki data lembur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
