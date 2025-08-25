<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $tripDateFilter = '';

    public function mount(): void
    {
        // Tetapkan tanggal HARI INI sebagai filter default saat komponen dimuat
        $this->tripDateFilter = today()->format('M j, Y');
    }

    /**
     * Listener event yang dipanggil dari JavaScript ketika tanggal diubah.
     */
    #[On('trip-date-updated')]
    public function updateTripChartData($date): void
    {
        // Perbarui properti dengan tanggal baru, atau kosongkan jika tidak ada
        $this->tripDateFilter = is_array($date) ? ($date['date'] ?? '') : $date;

        // Panggil fungsi untuk mengambil data berdasarkan filter tanggal yang baru
        $tripStatusData = $this->getFilteredTripData();
        $tripStatusChart = [
            'data' => $tripStatusData->values()->toArray(),
            'labels' => $tripStatusData->keys()->map(fn($status) => ucfirst($status))->toArray(),
        ];

        // Kirim event kembali ke browser dengan data yang sudah difilter
        $this->dispatch('trip-chart-updated', data: $tripStatusChart);
    }

    /**
     * Fungsi untuk mengambil data trip berdasarkan filter tanggal.
     * Logika ini sekarang andal karena format tanggal dari JS sudah dipastikan.
     */
    private function getFilteredTripData()
    {
        $tripQuery = Trip::query();

        if ($this->tripDateFilter) {
            // Gunakan " to " sebagai pemisah, sesuai dengan konfigurasi JS
            $dateParts = explode(' to ', $this->tripDateFilter);
            $startDate = Carbon::createFromFormat('M j, Y', trim($dateParts[0]));

            if (count($dateParts) == 2) {
                // Jika rentang tanggal
                $endDate = Carbon::createFromFormat('M j, Y', trim($dateParts[1]));
                $tripQuery->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            } else {
                // Jika hanya satu tanggal
                $tripQuery->whereDate('created_at', $startDate);
            }
        }

        return $tripQuery
            ->select('status_trip', DB::raw('count(*) as total'))
            ->groupBy('status_trip')
            ->pluck('total', 'status_trip');
    }

    private function getTopDriversData()
    {
        return Trip::with('user')
            ->where('status_trip', 'selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->groupBy('user_id')
            ->selectRaw("
                user_id,
                SUM(CASE WHEN jenis_trip = 'muatan perusahaan' THEN 1 ELSE 0 END) as trip_perusahaan,
                SUM(CASE WHEN jenis_trip = 'muatan driver' THEN 1 ELSE 0 END) as trip_driver,
                count(*) as total_trip
            ")
            ->orderByDesc('total_trip')
            ->limit(5)
            ->get();
    }

    /**
     * Fungsi render untuk memuat semua data dashboard.
     */
    public function render()
    {
        // Data untuk statistik utama (cards)
        $totalKaryawan = User::role('karyawan')->count();
        $totalDriver = User::role('driver')->count();
        $perjalananAktif = Trip::where('status_trip', 'proses')->count();
        $karyawanHadirHariIni = Attendance::whereDate('created_at', today())
            ->where('tipe_absensi', 'clock_in')
            ->distinct('user_id')
            ->count();

        // Data untuk chart perjalanan (HANYA untuk muat pertama kali)
        $initialTripChart = [
            'data' => $this->getFilteredTripData()->values()->toArray(),
            'labels' => $this->getFilteredTripData()->keys()->map(fn($status) => ucfirst($status))->toArray(),
        ];

        $topDrivers = $this->getTopDriversData();
        $userRoleData = DB::table('model_has_roles')->join('roles', 'model_has_roles.role_id', '=', 'roles.id')->select('roles.name', DB::raw('count(*) as total'))->groupBy('roles.name')->pluck('total', 'roles.name');
        $userRoleChart = ['labels' => $userRoleData->keys()->map(fn($role) => ucfirst($role)), 'data' => $userRoleData->values()];
        $absensiTerkini = Attendance::with('user')->latest()->take(6)->get();
        $izinTerkini = Izin::with('user')->latest()->take(3)->get();
        $lemburTerkini = Lembur::with('user')->latest()->take(3)->get();

        return view('livewire.dashboard', compact(
            'totalKaryawan',
            'totalDriver',
            'perjalananAktif',
            'karyawanHadirHariIni',
            'initialTripChart',
            'userRoleChart',
            'absensiTerkini',
            'izinTerkini',
            'lemburTerkini',
            'topDrivers'
        ));
    }
}
