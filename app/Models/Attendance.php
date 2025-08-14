<?php

namespace App\Models;

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
}
