<?php

namespace App\Models;

use Illuminate\Contracts\Concurrency\Driver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_name',
        'license_plate',
        'start_km',
        'end_km',
        'origin',
        'destination',
        'start_photo_path',
        'delivery_letter_path',
        'start_latitude',
        'start_longitude',
        'started_at',
        'status_trip',
        'status_lokasi',
        'status_muatan',
        'end_photo_path',
        'end_delivery_letter_path',
        'end_latitude',
        'end_longitude',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driverLocations()
    {
        return $this->hasMany(DriverLocation::class);
    }
}
