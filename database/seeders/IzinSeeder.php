<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IzinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Izin::query()->delete();

        $users = User::whereHas('roles', fn($q) => $q->where('name', 'karyawan'))->get();

        if ($users->isEmpty()) {
            $this->command->warn('No employees found to create leave data.');
            return;
        }

        // Ambil 2 user secara acak untuk diberi data izin
        $usersWithLeave = $users->random(2);

        foreach ($usersWithLeave as $user) {

            // Skenario 2: Izin kepentingan keluarga 3 hari (mulai hari ini)
            Izin::create([
                'user_id' => $user->id,
                'jenis_izin' => 'Kepentingan Keluarga',
                'tanggal_mulai' => Carbon::today()->format('Y-m-d'),
                'tanggal_selesai' => Carbon::today()->addDays(2)->format('Y-m-d'),
                'alasan' => 'Acara keluarga di luar kota.',
                'url_bukti' => 'bukti/keluarga.jpg',
            ]);
        }

        $this->command->info('Leave (Izin) data seeding completed.');
    }
}
