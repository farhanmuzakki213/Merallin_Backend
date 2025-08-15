<?php

namespace App\Models;

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
            get: fn () => $this->url_bukti ? Storage::url($this->url_bukti) : null,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
