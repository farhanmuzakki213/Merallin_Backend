<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Izin;
use App\Models\User;
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

    private function getStatusClasses(string $status): string
    {
        return match ($status) {
            'Sedang Bekerja' => 'bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-500',
            'Sudah Pulang' => 'bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-500',
            'Belum Hadir', 'Tidak Hadir', 'Tidak Clock-out' => 'bg-error-50 dark:bg-error-500/15 text-error-700 dark:text-error-500',
            default => 'bg-gray-100 dark:bg-gray-500/15 text-gray-700 dark:text-gray-400',
        };
    }

    public function render()
    {
        // --- 1. LOGIKA UNTUK TABEL ABSENSI (TETAP SAMA) ---
        $attendanceQuery = Attendance::query()
            ->with('user')
            ->whereHas('user', function ($q) {
                if ($this->search) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                }
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['karyawan', 'driver']);
                });
            });

        $startDate = today()->startOfDay();
        $endDate = today()->endOfDay();
        $isRange = false;

        if ($this->filterDate) {
            $dateParts = explode(' to ', $this->filterDate);
            $startDate = Carbon::createFromFormat('M j, Y', $dateParts[0])->startOfDay();
            if (count($dateParts) == 2) {
                $endDate = Carbon::createFromFormat('M j, Y', $dateParts[1])->endOfDay();
                $isRange = true;
            } else {
                $endDate = $startDate->copy()->endOfDay();
            }
        }

        $attendanceQuery->whereBetween('attendances.created_at', [$startDate, $endDate]);

        $allAttendances = $attendanceQuery->orderBy('created_at', 'desc')->get();
        $groupedByDayAndUser = $allAttendances->groupBy(fn($item) => Carbon::parse($item->created_at)->format('Y-m-d') . '_' . $item->user_id);

        $attendanceData = $groupedByDayAndUser->map(function ($dayUserGroup) {
            $user = $dayUserGroup->first()->user;
            if (!$user) return null;
            $clockIn = $dayUserGroup->firstWhere('tipe_absensi', 'datang');
            $clockOut = $dayUserGroup->firstWhere('tipe_absensi', 'pulang');
            $status = $this->filterDate ? ($clockIn ? ($clockOut ? 'Sudah Pulang' : 'Tidak Clock-out') : 'Tidak Hadir') : ($clockIn ? ($clockOut ? 'Sudah Pulang' : 'Sedang Bekerja') : 'Belum Hadir');

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'clock_in_data' => $clockIn,
                'clock_out_data' => $clockOut,
                'status' => $status,
                'status_classes' => $this->getStatusClasses($status),
                'attendance_date' => Carbon::parse($dayUserGroup->first()->created_at)->format('Y-m-d')
            ];
        })->filter()->sortByDesc(fn($item) => $item['attendance_date'] . '_' . $item['user_name'])->values();

        $paginatedAttendances = new LengthAwarePaginator($attendanceData->forPage(Paginator::resolveCurrentPage('page'), $this->perPage), $attendanceData->count(), $this->perPage, Paginator::resolveCurrentPage('page'), ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);

        // --- 2. LOGIKA DIPERBAIKI UNTUK TABEL KARYAWAN TIDAK HADIR ---
        $selectedDateTitle = $isRange ? $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y') : $startDate->format('d M Y');
        $absentUserData = collect();

        $allRelevantUsers = User::query()->whereHas('roles', fn($q) => $q->whereIn('name', ['karyawan', 'driver']))->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))->orderBy('name')->get();

        // Ambil semua data izin yang relevan dalam rentang tanggal untuk efisiensi
        $leaveRecords = Izin::where(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_mulai', '<=', $endDate)
                ->where('tanggal_selesai', '>=', $startDate);
        })->get()->keyBy(function ($item) {
            // Buat kunci unik untuk pencarian cepat nanti
            return $item->tanggal_mulai . '_' . $item->user_id;
        });

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateToCheck = $date->format('Y-m-d');
            $presentUserIds = Attendance::whereDate('created_at', $dateToCheck)->pluck('user_id')->unique();

            $notPresentUsers = $allRelevantUsers->whereNotIn('id', $presentUserIds);

            foreach ($notPresentUsers as $user) {
                $leave = Izin::where('user_id', $user->id)
                    ->where('tanggal_mulai', '<=', $dateToCheck)
                    ->where('tanggal_selesai', '>=', $dateToCheck)
                    ->first();

                $status = $leave ? $leave->jenis_izin : 'Tidak Hadir';

                $absentUserData->push([
                    'user' => $user,
                    'absent_date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'status_classes' => $this->getStatusClasses($status)
                ]);
            }
        }

        $paginatedAbsentUsers = new LengthAwarePaginator($absentUserData->forPage(Paginator::resolveCurrentPage('absentPage'), $this->perPage), $absentUserData->count(), $this->perPage, Paginator::resolveCurrentPage('absentPage'), ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'absentPage']);

        $itemsOnPage = collect($paginatedAbsentUsers->items());
        $paginatedGroupedAbsentUsers = $itemsOnPage->groupBy('absent_date')->sortKeysDesc();

        return view('livewire.attendanceTable.attendance-table', [
            'attendances' => $paginatedAttendances,
            'absentUsers' => $paginatedAbsentUsers,
            'groupedAbsentUsers' => $paginatedGroupedAbsentUsers,
            'selectedDate' => $selectedDateTitle,
        ]);
    }
}
