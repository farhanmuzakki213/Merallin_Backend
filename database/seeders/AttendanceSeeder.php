<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada
        Attendance::query()->delete();
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'karyawan')->orWhere('name', 'driver');
        })->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ditemukan user dengan role "karyawan" atau "driver".');
            $this->command->warn('Pastikan UserRoleSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        $this->command->info('Membuat data absensi untuk ' . $users->count() . ' user...');

        // Buat data absensi selama 10 hari terakhir untuk setiap user
        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) {
                // Tentukan tanggal absensi
                $date = Carbon::now()->subDays($i);

                // Buat absensi datang
                Attendance::create([
                    'user_id' => $user->id,
                    'photo_path' => 'photos/dummy_datang.jpg',
                    'latitude' => -6.914744, // Contoh latitude Bandung
                    'longitude' => 107.609810, // Contoh longitude Bandung
                    'tipe_absensi' => 'datang',
                    // Tentukan status acak, 80% tepat waktu
                    'status_absensi' => (rand(1, 10) <= 8) ? 'Tepat waktu' : 'Terlambat',
                    'created_at' => $date->copy()->setHour(8)->setMinutes(rand(0, 30))->setSeconds(rand(0, 59)),
                    'updated_at' => $date->copy()->setHour(8)->setMinutes(rand(0, 30))->setSeconds(rand(0, 59)),
                ]);

                // Buat absensi pulang (70% kemungkinan user sudah absen pulang)
                if (rand(1, 10) <= 7) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'photo_path' => 'photos/dummy_pulang.jpg',
                        'latitude' => -6.914744,
                        'longitude' => 107.609810,
                        'tipe_absensi' => 'pulang',
                        'status_absensi' => 'Tepat waktu', // Asumsikan pulang selalu tepat waktu
                        'created_at' => $date->copy()->setHour(17)->setMinutes(rand(0, 59))->setSeconds(rand(0, 59)),
                        'updated_at' => $date->copy()->setHour(17)->setMinutes(rand(0, 59))->setSeconds(rand(0, 59)),
                    ]);
                }
            }
        }
        $this->command->info('Data absensi berhasil dibuat.');
    }
}
