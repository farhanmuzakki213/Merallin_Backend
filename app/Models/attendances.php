<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attendances extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_path',
        'latitude',
        'longitude',
        'is_mocked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
