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
        $role_direksi = Role::firstOrCreate(['name' => 'direksi']);
        $role_manager = Role::firstOrCreate(['name' => 'manager']);


        // Create Admin User

        $manager = User::factory()->create([
            'name' => 'Nyangnyang Hapidin',
            'email' => 'nyangnyang.ops@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '085861201909',
            'password' => Hash::make('password')
        ]);
        $manager->assignRole($role_manager);

        $direksi = User::factory()->create([
            'name' => 'Michael Antonius Miru',
            'email' => 'miru.direksi@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '082127309984',
            'password' => Hash::make('password')
        ]);
        $direksi->assignRole($role_direksi);

        $direksi = User::factory()->create([
            'name' => 'Rizky Dhafin Fauzan',
            'email' => 'rdhafin.direksi@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '082130333021',
            'password' => Hash::make('password')
        ]);
        $direksi->assignRole($role_direksi);

        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $admin->assignRole($role_admin);

        $karyawan = User::factory()->create([
            'name' => 'Farhan Muzakki',
            'email' => 'farhan.it@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '081268772052',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        $karyawan = User::factory()->create([
            'name' => 'M. Zidhan Prasetyo',
            'email' => 'zidhan.it@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '083187407180',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        $karyawan = User::factory()->create([
            'name' => 'Rifko Ahmad',
            'email' => 'rifko.adm@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '081365121468',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        $karyawan = User::factory()->create([
            'name' => 'Winaldo Ageng K',
            'email' => 'ageng.adm@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '0895616031287',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        $karyawan = User::factory()->create([
            'name' => 'M. Ar-Razi A.Gazali',
            'email' => 'razi.desain@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '085271339592',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        $karyawan = User::factory()->create([
            'name' => 'Fadlan Al`Farisi',
            'email' => 'fadlan.desain@merallin.group',
            'alamat' => 'Jl. Merdeka No. 1, Bandung',
            'no_telepon' => '083168667549',
            'password' => Hash::make('password')
        ]);
        $karyawan->assignRole($role_karyawan);

        // Create Driver User
        $driver1 = User::factory()->create([
            'name' => 'Driver1 User',
            'email' => 'driver1@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $driver1->assignRole($role_driver);
        $driver2 = User::factory()->create([
            'name' => 'Driver2 User',
            'email' => 'driver2@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $driver2->assignRole($role_driver);
        $driver3 = User::factory()->create([
            'name' => 'Driver3 User',
            'email' => 'driver3@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $driver3->assignRole($role_driver);
        $driver4 = User::factory()->create([
            'name' => 'Driver4 User',
            'email' => 'driver4@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $driver4->assignRole($role_driver);
        $driver5 = User::factory()->create([
            'name' => 'Driver5 User',
            'email' => 'driver5@example.com',
            'alamat' => 'Jl. Sudirman No. 2, Bandung',
            'no_telepon' => '081234567002',
            'password' => Hash::make('password')
        ]);
        $driver5->assignRole($role_driver);
    }
}
