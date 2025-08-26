<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'alamat',
        'no_telepon',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'profile_photo_url',
    ];

    /* *
     * Ambil URL untuk gambar profil pengguna.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        // dd(config('filesystems.disks.public'));
        if ($this->profile_photo_path) {
            return $this->profile_photo_path;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function izins()
    {
        return $this->hasMany(Izin::class);
    }

    public function lemburs()
    {
        return $this->hasMany(Lembur::class);
    }

    public function vehicleLocations()
    {
        return $this->hasMany(vehicleLocation::class);
    }
}
