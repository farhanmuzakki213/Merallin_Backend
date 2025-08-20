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

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        // 1. DATA UNTUK STATISTIK UTAMA (CARDS)
        $totalKaryawan = User::role('karyawan')->count();
        $totalDriver = User::role('driver')->count();
        $perjalananAktif = Trip::where('status_trip', 'proses')->count();
        $karyawanHadirHariIni = Attendance::whereDate('created_at', today())
            ->where('tipe_absensi', 'clock_in')
            ->distinct('user_id')
            ->count();

        // 2. DATA UNTUK GRAFIK
        // Grafik Status Perjalanan (Bar Chart)
        $tripStatusData = Trip::query()
            ->select('status_trip', DB::raw('count(*) as total'))
            ->groupBy('status_trip')
            ->pluck('total', 'status_trip');

        $tripStatusChart = [
            'labels' => $tripStatusData->keys()->map(fn ($status) => ucfirst($status)),
            'data' => $tripStatusData->values(),
        ];

        // Grafik Distribusi Role (Donut Chart)
        $userRoleData = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'roles.name');

        $userRoleChart = [
            'labels' => $userRoleData->keys()->map(fn ($role) => ucfirst($role)),
            'data' => $userRoleData->values(),
        ];

        // 3. DATA UNTUK TABEL AKTIVITAS TERKINI
        $absensiTerkini = Attendance::with('user')->latest()->take(6)->get();
        $izinTerkini = Izin::with('user')->latest()->take(3)->get();
        $lemburTerkini = Lembur::with('user')->latest()->take(3)->get();


        return view('livewire.dashboard', [
            // Data untuk Cards
            'totalKaryawan' => $totalKaryawan,
            'totalDriver' => $totalDriver,
            'perjalananAktif' => $perjalananAktif,
            'karyawanHadirHariIni' => $karyawanHadirHariIni,
            // Data untuk Charts
            'tripStatusChart' => $tripStatusChart,
            'userRoleChart' => $userRoleChart,
            // Data untuk Tabel
            'absensiTerkini' => $absensiTerkini,
            'izinTerkini' => $izinTerkini,
            'lemburTerkini' => $lemburTerkini,
        ]);
    }
}
