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

class SalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_path',
        'period',
        'uuid',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'period' => 'date',
    ];

    protected $appends = [
        'file_url',
        'file',
    ];

    /**
     * Menggunakan event 'creating' untuk memastikan setiap slip gaji baru
     * akan memiliki UUID yang unik secara otomatis sebelum disimpan ke database.
     */
    protected static function booted(): void
    {
        static::creating(function ($salarySlip) {
            if (empty($salarySlip->uuid)) {
                $salarySlip->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Perbaikan: Accessor getFileUrlAttribute.
     * Menghasilkan URL ke route 'salary-slips.share' dengan parameter UUID,
     * yang merupakan URL berbagi yang aman.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->uuid ? route('salary-slips.share', $this->uuid) : null;
    }

    public function getFileAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    /**
     * Penambahan: Accessor untuk mendapatkan nama file asli dari file_path.
     * Ini berguna untuk ditampilkan di UI saat mengedit.
     */
    public function getOriginalFileNameAttribute(): ?string
    {
        return basename($this->file_path);
    }

    // --- Accessor Waktu (Tidak diubah) ---
    protected function createdAt(): Attribute
    {
        return Attribute::make(get: fn ($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'));
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(get: fn ($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'));
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::parse($date)->setTimezone('Asia/Jakarta')->toIso8601String();
    }
}
