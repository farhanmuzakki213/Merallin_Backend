<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Izin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'url_bukti',
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
     * Tambahkan atribut 'full_url_bukti' ke dalam JSON response.
     */
    protected $appends = ['full_url_bukti'];

    /**
     * Definisikan Accessor untuk mendapatkan URL lengkap dari file bukti.
     * Atribut ini akan otomatis ditambahkan sebagai 'full_url_bukti'.
     */
    protected function fullUrlBukti(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->url_bukti ? Storage::url($this->url_bukti) : null,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
