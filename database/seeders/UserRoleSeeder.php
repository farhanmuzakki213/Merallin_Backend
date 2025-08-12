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
        $role_admin = Role::firstOrCreate(['name' => 'admin']);


        // Create Admin User
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $admin->assignRole($role_admin);

        // Create Karyawan User
        $karyawan = User::factory()->create([
            'name' => 'Karyawan User',
            'email' => 'karyawan@example.com',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '081234567001',
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
