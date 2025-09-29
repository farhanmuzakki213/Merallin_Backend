<?php

namespace App\Http\Requests;

use App\Helpers\DateHelper;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreLemburRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Izinkan semua user yang sudah terautentikasi untuk membuat request ini
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_hari'        => 'required|in:Kerja,Libur,Libur Nasional',
            'department'        => 'required|in:Finance,HRD,IT,Admin,Manager Operasional',
            'tanggal_lembur'    => 'required|date',
            'keterangan_lembur' => 'required|string',
            'mulai_jam_lembur'  => 'required|date_format:H:i',
            'selesai_jam_lembur'=> 'required|date_format:H:i|after:mulai_jam_lembur',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                // Pastikan semua data dasar ada sebelum melanjutkan
                if (!$this->has(['tanggal_lembur', 'mulai_jam_lembur', 'selesai_jam_lembur'])) {
                    return;
                }

                $tanggalLembur = Carbon::parse($this->input('tanggal_lembur'));
                $jamMulai = Carbon::parse($this->input('mulai_jam_lembur'));
                $jamSelesai = Carbon::parse($this->input('selesai_jam_lembur'));

                // Hitung durasi pengajuan lembur dalam jam
                $durasiPengajuanJam = round($jamMulai->diffInMinutes($jamSelesai) / 60, 2);

                // ===== VALIDASI 1: BATASAN DURASI HARIAN =====
                $isHariLibur = DateHelper::isNationalHoliday($tanggalLembur) || $tanggalLembur->isWeekend();

                if ($isHariLibur) {
                    if ($durasiPengajuanJam < 2) {
                        $validator->errors()->add('selesai_jam_lembur', 'Durasi lembur di hari libur minimal adalah 2 jam.');
                    }
                    if ($durasiPengajuanJam > 5) {
                        $validator->errors()->add('selesai_jam_lembur', 'Durasi lembur di hari libur maksimal adalah 5 jam.');
                    }
                } else { // Hari Kerja
                    if ($durasiPengajuanJam < 1) {
                        $validator->errors()->add('selesai_jam_lembur', 'Durasi lembur di hari kerja minimal adalah 1 jam.');
                    }
                    if ($durasiPengajuanJam > 3) {
                        $validator->errors()->add('selesai_jam_lembur', 'Durasi lembur di hari kerja maksimal adalah 3 jam.');
                    }
                }

                // Jika sudah ada error dari validasi harian, tidak perlu lanjut ke validasi mingguan
                if ($validator->errors()->any()) {
                    return;
                }

                // ===== VALIDASI 2: BATASAN KUOTA JAM MINGGUAN =====
                $startOfWeek = $tanggalLembur->copy()->startOfWeek(Carbon::MONDAY);
                $endOfWeek = $tanggalLembur->copy()->endOfWeek(Carbon::SUNDAY);

                // Ambil total jam dari lembur yang sudah disetujui/diajukan pada minggu yang sama
                $totalJamMingguan = Lembur::where('user_id', Auth::id())
                    ->whereBetween('tanggal_lembur', [$startOfWeek, $endOfWeek])
                    // Hanya hitung lembur yang tidak ditolak untuk mencegah pengajuan ganda
                    ->where('status_lembur', '!=', 'Ditolak')
                    ->sum('total_jam'); // Asumsi Anda menyimpan durasi rencana di 'total_jam' atau field serupa

                if (($totalJamMingguan + $durasiPengajuanJam) > 20) {
                    $sisaJam = 20 - $totalJamMingguan;
                    $validator->errors()->add(
                        'selesai_jam_lembur',
                        'Pengajuan gagal. Anda akan melebihi batas 20 jam lembur per minggu. Sisa kuota Anda minggu ini adalah ' . ($sisaJam > 0 ? $sisaJam : 0) . ' jam.'
                    );
                }
            }
        ];
    }
}
