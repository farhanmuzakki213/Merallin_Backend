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
        Attendance::query()->delete();

        $users = User::whereHas('roles', fn($q) => $q->where('name','karyawan'))->get();

        if ($users->isEmpty()) {
            $this->command->warn('No employees or drivers found.');
            return;
        }

        $this->command->info('Creating attendance data for ' . $users->count() . ' users...');

        // Buat data absensi selama 15 hari terakhir
        for ($i = 0; $i < 15; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($users as $user) {
                // 80% kemungkinan user akan absen pada hari ini
                if (rand(1, 100) > 80) {
                    continue; // Lewati user ini untuk hari ini, membuatnya "tidak hadir"
                }

                // Buat absensi datang
                $clockInTime = $date->copy()->setHour(8)->setMinutes(rand(0, 45));
                Attendance::create([
                    'user_id' => $user->id,
                    'photo_path' => 'photos/dummy_datang.jpg',
                    'latitude' => -6.914744,
                    'longitude' => 107.609810,
                    'tipe_absensi' => 'datang',
                    'status_absensi' => (rand(1, 10) <= 8) ? 'Tepat waktu' : 'Terlambat',
                    'created_at' => $clockInTime,
                    'updated_at' => $clockInTime,
                ]);

                // 70% kemungkinan user sudah absen pulang
                if (rand(1, 10) <= 7) {
                    $clockOutTime = $date->copy()->setHour(17)->setMinutes(rand(0, 59));
                    Attendance::create([
                        'user_id' => $user->id,
                        'photo_path' => 'photos/dummy_pulang.jpg',
                        'latitude' => -6.914744,
                        'longitude' => 107.609810,
                        'tipe_absensi' => 'pulang',
                        'status_absensi' => 'Tepat waktu',
                        'created_at' => $clockOutTime,
                        'updated_at' => $clockOutTime,
                    ]);
                }
            }
        }
        $this->command->info('Attendance data seeding completed.');
    }
}
