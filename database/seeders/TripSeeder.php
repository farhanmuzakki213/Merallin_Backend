<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data trip lama agar tidak ada duplikat
        Trip::truncate();

        // Ambil beberapa user dengan role 'driver' untuk dijadikan contoh
        // Pastikan Anda sudah punya user dengan role ini.
        $drivers = User::role('driver')->pluck('id')->toArray();

        // Jika tidak ada driver, batalkan seeder
        if (empty($drivers)) {
            $this->command->info('Tidak ada user dengan role "driver". Seeder Trip dibatalkan.');
            return;
        }

        $trips = [
            // --- Data Hari Ini (20 Agustus 2025) ---
            ['Proyek Renovasi JKT', 'Gudang Cikarang', 'Site Proyek Sudirman', 'proses', Carbon::today()],
            ['Pengiriman Material Baja', 'Pabrik Bekasi', 'Pelabuhan Tj. Priok', 'selesai', Carbon::today()->subHours(5)],
            ['Trip Logistik Internal', 'Kantor Pusat', 'Gudang Marunda', 'tersedia', Carbon::today()->subHours(2)],

            // --- Data Kemarin (19 Agustus 2025) ---
            ['Proyek Gedung Bintaro', 'Gudang Pulo Gadung', 'Site Proyek Bintaro', 'selesai', Carbon::yesterday()],
            ['Pengiriman Semen', 'Pabrik Cibinong', 'Distributor Bogor', 'selesai', Carbon::yesterday()->subHours(3)],

            // --- Data Minggu Lalu (13 - 14 Agustus 2025) ---
            ['Proyek Jembatan Serpong', 'Gudang Cikarang', 'Site Proyek BSD', 'selesai', Carbon::today()->subDays(7)],
            ['Pengiriman Alat Berat', 'Gudang Marunda', 'Proyek Karawang', 'selesai', Carbon::today()->subDays(6)],
            ['Trip Cadangan', 'Kantor Pusat', 'Pool Kendaraan', 'tersedia', Carbon::today()->subDays(6)],

            // --- Data Awal Bulan (1 - 3 Agustus 2025) ---
            ['Proyek Apartemen Depok', 'Gudang Cibinong', 'Site Proyek Margonda', 'selesai', Carbon::parse('2025-08-01')],
            ['Pengiriman Keramik', 'Distributor Bandung', 'Toko Material Depok', 'selesai', Carbon::parse('2025-08-02')],
            ['Material Pameran', 'JCC Senayan', 'Gudang Cawang', 'selesai', Carbon::parse('2025-08-03')],

            // --- Data Bulan Lalu (Juli 2025) ---
            ['Proyek Villa Puncak', 'Gudang Bogor', 'Site Proyek Cipanas', 'selesai', Carbon::parse('2025-07-25')],
            ['Pengiriman Besi Beton', 'Pabrik Cilegon', 'Proyek PIK 2', 'selesai', Carbon::parse('2025-07-28')],

            // --- Data Tersedia Untuk Masa Depan ---
            ['Pengiriman Rutin', 'Gudang Cikarang', 'Distributor Karawang', 'tersedia', Carbon::today()],
            ['Jadwal Pengiriman Pagi', 'Gudang Pulo Gadung', 'Proyek Bekasi Timur', 'tersedia', Carbon::today()],
        ];

        foreach ($trips as $tripData) {
            Trip::create([
                'user_id'       => ($tripData[3] !== 'tersedia') ? $drivers[array_rand($drivers)] : null,
                'project_name'  => $tripData[0],
                'origin'        => $tripData[1],
                'destination'   => $tripData[2],
                'status_trip'   => $tripData[3],
                'created_at'    => $tripData[4],
                'updated_at'    => $tripData[4],
            ]);
        }
    }
}
