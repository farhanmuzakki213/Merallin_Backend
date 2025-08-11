<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    protected $fillable = [
        'trip_id',
        'location',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
