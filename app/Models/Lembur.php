<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'persetujuan_manajer',
        'persetujuan_direksi',
    ];

    /**
     * Mendapatkan user yang memiliki data lembur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
