<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_path',
        'latitude',
        'longitude',
        'tipe_absensi',
        'status_absensi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return Storage::url($this->photo_path);
        }

        return 'https://ui-avatars.com/api/?name=N+A&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Mengubah format created_at ke zona waktu WIB saat diakses.
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'),
        );
    }

    /**
     * Mengubah format updated_at ke zona waktu WIB saat diakses.
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->setTimezone('Asia/Jakarta'),
        );
    }

    /**
     * Menyesuaikan format tanggal saat model diubah menjadi array atau JSON.
     * Ini akan menimpa perilaku default Laravel yang memaksa ke UTC.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::parse($date)
            ->setTimezone('Asia/Jakarta')
            ->toIso8601String();
    }
}
