<?php

namespace App\Livewire;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Attendance Data Table')]
class AttendanceTable extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';

    public $filterDate = '';

    #[On('date-updated')]
    public function updateDateFilter($date = null)
    {
        $this->filterDate = $date;
        $this->resetPage();
    }

    public function render()
    {
         // 1. Query dasar untuk mengambil data absensi
        $query = Attendance::query()
            ->with('user')
            ->whereHas('user', function ($q) {
                // Filter berdasarkan pencarian nama
                if ($this->search) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                }
                // Filter hanya untuk role tertentu
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['karyawan', 'driver']);
                });
            });

        // 2. Terapkan filter rentang tanggal jika ada, jika tidak, gunakan tanggal hari ini
        if ($this->filterDate) {
            $dateParts = explode(' to ', $this->filterDate);
            $startDate = Carbon::createFromFormat('M j, Y', $dateParts[0])->startOfDay();
            $endDate = count($dateParts) == 2 ? Carbon::createFromFormat('M j, Y', $dateParts[1])->endOfDay() : $startDate->copy()->endOfDay();
            $query->whereBetween('attendances.created_at', [$startDate, $endDate]);
        } else {
            $query->whereDate('attendances.created_at', today());
        }

        // 3. Ambil semua data yang cocok dari database
        $allAttendances = $query->orderBy('created_at', 'desc')->get();

        // 4. Kelompokkan data berdasarkan hari dan ID pengguna
        $groupedByDayAndUser = $allAttendances->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d') . '_' . $item->user_id;
        });

        // 5. Transformasi data yang dikelompokkan ke dalam format yang diinginkan
        $attendanceData = $groupedByDayAndUser->map(function ($dayUserGroup) {
            $user = $dayUserGroup->first()->user;

            if (!$user) {
                return null;
            }

            $clockIn = $dayUserGroup->firstWhere('tipe_absensi', 'datang');
            $clockOut = $dayUserGroup->firstWhere('tipe_absensi', 'pulang');

            // Logika untuk menentukan status harian pengguna
            $status = 'Belum Hadir';
            if ($this->filterDate) {
                // Logika status untuk tampilan riwayat/filter
                $status = $clockIn ? ($clockOut ? 'Sudah Pulang' : 'Tidak Clock-out') : 'Tidak Hadir';
            } elseif ($clockIn) {
                // Logika status untuk tampilan hari ini
                $status = $clockOut ? 'Sudah Pulang' : 'Sedang Bekerja';
            }

            // Kembalikan data dalam format array yang Anda tentukan
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'clock_in_data' => $clockIn,
                'clock_out_data' => $clockOut,
                'status' => $status,
                'attendance_date' => Carbon::parse($dayUserGroup->first()->created_at)->format('Y-m-d'),
            ];
        })->filter(); // Hapus item null jika ada user yang tidak ditemukan

        // 6. [PENTING] Urutkan kembali data untuk memastikan urutan konsisten
        $sortedAttendanceData = $attendanceData->sortByDesc(function ($item) {
            // Urutkan berdasarkan tanggal (terbaru dulu), lalu nama untuk urutan yang stabil
            return $item['attendance_date'] . '_' . $item['user_name'];
        })->values(); // `values()` untuk mereset index array

        // 7. Buat paginasi secara manual dari data yang sudah diproses dan diurutkan
        $currentPage = Paginator::resolveCurrentPage('page');
        $pagedData = $sortedAttendanceData->slice(($currentPage - 1) * $this->perPage, $this->perPage)->all();
        $paginatedData = new LengthAwarePaginator(
            $pagedData,
            $sortedAttendanceData->count(),
            $this->perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
        return view('livewire.attendanceTable.attendance-table', [
            'attendances' => $paginatedData,
        ]);
    }
}
