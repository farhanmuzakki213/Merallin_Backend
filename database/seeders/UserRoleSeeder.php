<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $role_karyawan = Role::firstOrCreate(['name' => 'karyawan']);
        $role_driver = Role::firstOrCreate(['name' => 'driver']);

        // Create Karyawan User
        $karyawan = User::factory()->create([
            'name' => 'Karyawan User',
            'email' => 'karyawan@example.com',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',   // <-- TAMBAHAN
            'no_telepon' => '081234567001',             // <-- TAMBAHAN
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        // Create Driver User
        $driver = User::factory()->create([
            'name' => 'Driver User',
            'email' => 'driver@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung', // <-- TAMBAHAN
            'no_telepon' => '081234567002',            // <-- TAMBAHAN
            'password' => Hash::make('password')
        ]);
        $driver->assignRole($role_driver);
    }
}
