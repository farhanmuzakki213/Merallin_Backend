<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Lembur;
use Carbon\Carbon;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Hapus data lama untuk menghindari duplikasi saat seeding ulang
        Lembur::query()->delete();

        // Ambil semua ID user yang ada di database
        // Pastikan Anda sudah memiliki data user sebelum menjalankan seeder ini
        $userIds = User::pluck('id')->toArray();

        // Jika tidak ada user, hentikan seeder dan beri pesan
        if (empty($userIds)) {
            $this->command->info('Tidak ada data user. Silakan buat data user terlebih dahulu.');
            return;
        }

        // Definisikan pilihan untuk kolom enum
        $jenisHariOptions = ['Kerja', 'Libur', 'Libur Nasional'];
        $departmentOptions = ['Finance', 'Manager Operasional', 'HRD', 'IT', 'Admin'];
        $approvalStatusOptions = ['Ditolak', 'Diterima', 'Menunggu Persetujuan'];

        // Keterangan lembur yang umum
        $keteranganLemburSamples = [
            'Menyelesaikan laporan keuangan bulanan.',
            'Melakukan perbaikan darurat pada server utama.',
            'Mempersiapkan materi presentasi untuk rapat direksi.',
            'Menginput data karyawan baru ke sistem HRIS.',
            'Menangani keluhan pelanggan di luar jam kerja.',
            'Update sistem aplikasi ke versi terbaru.',
            'Stock opname gudang akhir bulan.',
            'Mendampingi audit eksternal.',
        ];

        $dataLembur = [];

        // Buat 30 data dummy
        for ($i = 0; $i < 30; $i++) {
            // Tentukan tanggal lembur secara acak dalam 3 bulan terakhir
            $tanggalLembur = Carbon::now()->subDays(rand(0, 90));

            // Tentukan jam mulai lembur secara acak (antara jam 17:00 dan 19:00)
            $mulaiJamLembur = Carbon::createFromTime(rand(17, 19), rand(0, 59), 0);

            // Tentukan durasi lembur (antara 2 sampai 4 jam)
            $durasiLembur = rand(2, 4);
            $selesaiJamLembur = $mulaiJamLembur->copy()->addHours($durasiLembur)->addMinutes(rand(0, 59));

            $dataLembur[] = [
                'user_id' => $userIds[array_rand($userIds)],
                'jenis_hari' => $jenisHariOptions[array_rand($jenisHariOptions)],
                'department' => $departmentOptions[array_rand($departmentOptions)],
                'tanggal_lembur' => $tanggalLembur->toDateString(),
                'keterangan_lembur' => $keteranganLemburSamples[array_rand($keteranganLemburSamples)],
                'mulai_jam_lembur' => $mulaiJamLembur->toTimeString(),
                'selesai_jam_lembur' => $selesaiJamLembur->toTimeString(),
                'status_lembur' => $approvalStatusOptions[array_rand($approvalStatusOptions)],
                'persetujuan_direksi' => $approvalStatusOptions[array_rand($approvalStatusOptions)],
                // 'persetujuan_manajer' => $approvalStatusOptions[array_rand($approvalStatusOptions)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Masukkan semua data ke database dalam satu query
        DB::table('lemburs')->insert($dataLembur);
    }
}
