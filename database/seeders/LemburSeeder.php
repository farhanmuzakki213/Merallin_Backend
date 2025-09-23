<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lemburs')->delete();

        // Ambil 3 user karyawan secara acak untuk dibuatkan data
        $karyawan = User::role('karyawan')->inRandomOrder()->take(3)->get();

        if ($karyawan->count() < 3) {
            $this->command->info('Karyawan tidak cukup untuk membuat data dummy (minimal 3). Seeder dilewati.');
            return;
        }

        $this->command->info('Membuat data lembur dummy untuk 3 karyawan...');

        // Daftar hari libur nasional fiktif untuk contoh
        $liburNasional = [
            // Contoh: '2023-12-25',
        ];

        $keteranganOptions = [
            'Menyelesaikan laporan akhir bulan', 'Mengerjakan revisi mendadak dari klien',
            'Persiapan audit internal', 'Perbaikan bug kritis pada sistem', 'Rapat evaluasi proyek'
        ];
        $departmentOptions = ['IT', 'Admin', 'Finance'];

        foreach ($karyawan as $user) {
            $totalJamLemburMingguan = 0;
            $jumlahLemburDibuat = rand(1, 5); // Setiap user punya 1-5 data lembur
            $tanggalDibuat = []; // Untuk memastikan tidak ada lembur di hari yang sama

            for ($i = 0; $i < $jumlahLemburDibuat; $i++) {
                // Pilih tanggal acak dalam 7 hari terakhir, pastikan unik
                do {
                    $tanggalLembur = Carbon::now()->subDays(rand(0, 6));
                } while (in_array($tanggalLembur->format('Y-m-d'), $tanggalDibuat));

                $tanggalDibuat[] = $tanggalLembur->format('Y-m-d');
                $isHariKerja = $tanggalLembur->isWeekday();
                $isLiburNasional = in_array($tanggalLembur->format('Y-m-d'), $liburNasional);

                $jenisHari = 'Kerja';
                if ($isLiburNasional) {
                    $jenisHari = 'Libur Nasional';
                } elseif (!$isHariKerja) {
                    $jenisHari = 'Libur';
                }

                // Tentukan durasi lembur berdasarkan jenis hari
                if ($jenisHari == 'Kerja') {
                    $durasiJam = rand(1, 3);
                } else { // Hari Libur atau Libur Nasional
                    $durasiJam = rand(2, 5);
                }

                // Pastikan tidak melebihi batas 20 jam seminggu
                if (($totalJamLemburMingguan + $durasiJam) > 20) {
                    continue; // Lewati jika akan melebihi batas
                }
                $totalJamLemburMingguan += $durasiJam;

                // Tentukan jam mulai lembur
                $mulaiJamLembur = Carbon::parse($tanggalLembur->format('Y-m-d') . ' 17:' . rand(0, 59) . ':00');
                if($jenisHari != 'Kerja') {
                    $mulaiJamLembur = Carbon::parse($tanggalLembur->format('Y-m-d') . ' 09:' . rand(0, 59) . ':00');
                }
                $selesaiJamLembur = $mulaiJamLembur->copy()->addHours($durasiJam);

                // Tentukan status persetujuan
                $isDisetujui = (rand(1, 10) > 3); // 70% kemungkinan disetujui
                $statusLembur = $isDisetujui ? 'Diterima' : 'Ditolak';
                $persetujuanDireksi = $isDisetujui ? 'Diterima' : 'Ditolak';

                $dataLembur = [
                    'user_id' => $user->id,
                    'uuid' => Str::uuid(),
                    'jenis_hari' => $jenisHari,
                    'department' => $departmentOptions[array_rand($departmentOptions)],
                    'tanggal_lembur' => $tanggalLembur->format('Y-m-d'),
                    'keterangan_lembur' => $keteranganOptions[array_rand($keteranganOptions)],
                    'mulai_jam_lembur' => $mulaiJamLembur->format('H:i:s'),
                    'selesai_jam_lembur' => $selesaiJamLembur->format('H:i:s'),
                    'status_lembur' => $statusLembur,
                    'persetujuan_direksi' => $persetujuanDireksi,
                    'alasan' => !$isDisetujui ? 'Beban kerja tidak mendesak' : null,
                    'file_path' => null,
                    'created_at' => $tanggalLembur,
                    'updated_at' => $tanggalLembur,
                ];

                // HANYA JIKA DISETUJUI, buat data clock-in/out aktual
                if ($isDisetujui) {
                    $dataLembur['jam_mulai_aktual'] = $mulaiJamLembur->copy()->addMinutes(rand(-5, 5));
                    $dataLembur['jam_selesai_aktual'] = $selesaiJamLembur->copy()->addMinutes(rand(-5, 5));
                    $dataLembur['lokasi_mulai'] = json_encode(['latitude' => -6.9175, 'longitude' => 107.6191]);
                    $dataLembur['lokasi_selesai'] = json_encode(['latitude' => -6.9175, 'longitude' => 107.6191]);
                }

                Lembur::create($dataLembur);
            }
        }

        $this->command->info('Seeder data lembur yang realistis berhasil dijalankan.');
    }
}
