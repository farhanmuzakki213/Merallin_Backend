<div>
    <div x-data="{ pageName: 'Daily Attendances' }">
        @include('partials.breadcrumb')
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Bagian Kontrol Filter (Tidak ada perubahan) --}}
            <div class="mb-4 flex flex-col gap-3 px-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400"> Show </span>
                    <div class="relative z-20 bg-transparent">
                        <select wire:model.live="perPage"
                            class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                        <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400"> entries </span>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <input id="attendance-datepicker" wire:key="attendance-datepicker"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            placeholder="Filter by date..." data-class="right-0" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="text" placeholder="Search by name..." wire:model.live.debounce.300ms="search"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1100px]">
                    {{-- Header Tabel: Dibuat statis/tetap untuk semua kondisi --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                        <div
                            class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Date</p>
                        </div>
                        <div
                            class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">User</p>
                        </div>
                        <div
                            class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Clock-In</p>
                        </div>
                        <div
                            class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Clock-Out</p>
                        </div>
                        <div class="col-span-2 flex items-center px-4 py-3">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                        </div>
                    </div>

                    {{-- Body Tabel: Dibuat statis dengan kolom tanggal yang selalu tampil --}}
                    @forelse ($attendances as $item)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            {{-- Kolom Tanggal (Selalu ditampilkan) --}}
                            <div
                                class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ \Carbon\Carbon::parse($item['attendance_date'])->format('d M Y') }}</p>
                            </div>

                            {{-- Kolom User --}}
                            <div
                                class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <p class="text-theme-sm block font-medium text-gray-800 dark:text-white/90">
                                        {{ $item['user_name'] }}</p>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400">{{ $item['user_email'] }}</span>
                                </div>
                            </div>

                            {{-- Kolom Absen Datang --}}
                            <div class="col-span-3 border-r border-gray-100 p-3 dark:border-gray-800">
                                @if ($clock_in = $item['clock_in_data'])
                                    <div class="flex items-start gap-3">
                                        <img src="{{ $clock_in->photo_url }}" alt="Photo"
                                            class="h-14 w-14 rounded-md object-cover">
                                        <div class="text-xs">
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{ \Carbon\Carbon::parse($clock_in->created_at)->format('H:i:s') }}</p>
                                            <a href="http://maps.google.com/maps?q={{ $clock_in->latitude }},{{ $clock_in->longitude }}"
                                                target="_blank" class="text-blue-500 hover:underline">Lihat Lokasi</a>
                                            <p><span
                                                    class="rounded-full px-2 py-0.5 {{ $clock_in->status_absensi == 'Tepat waktu' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $clock_in->status_absensi }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>

                            {{-- Kolom Absen Pulang --}}
                            <div class="col-span-2 border-r border-gray-100 p-3 dark:border-gray-800">
                                @if ($clock_out = $item['clock_out_data'])
                                    <div class="flex items-start gap-3">
                                        <img src="{{ $clock_out->photo_url }}" alt="Photo"
                                            class="h-14 w-14 rounded-md object-cover">
                                        <div class="text-xs">
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{ \Carbon\Carbon::parse($clock_out->created_at)->format('H:i:s') }}
                                            </p>
                                            <a href="http://maps.google.com/maps?q={{ $clock_out->latitude }},{{ $clock_out->longitude }}"
                                                target="_blank" class="text-blue-500 hover:underline">Lihat Lokasi</a>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>

                            {{-- Kolom Status Harian --}}
                            <div class="col-span-2 flex items-center px-4 py-3">
                                @php
                                    $status = $item['status'];
                                    $bgColor = 'bg-gray-100 dark:bg-gray-500/15';
                                    $textColor = 'text-gray-700 dark:text-gray-400';

                                    if (in_array($status, ['Sedang Bekerja'])) {
                                        $bgColor = 'bg-success-50 dark:bg-success-500/15';
                                        $textColor = 'text-success-700 dark:text-success-500';
                                    } elseif (in_array($status, ['Belum Hadir', 'Tidak Hadir', 'Tidak Absen Pulang'])) {
                                        $bgColor = 'bg-error-50 dark:bg-error-500/15';
                                        $textColor = 'text-error-700 dark:text-error-500';
                                    } elseif ($status === 'Sudah Pulang') {
                                        $bgColor = 'bg-blue-50 dark:bg-blue-500/15';
                                        $textColor = 'text-blue-700 dark:text-blue-500';
                                    }
                                @endphp
                                <p
                                    class="{{ $bgColor }} {{ $textColor }} text-theme-xs rounded-full px-2 py-0.5 font-medium">
                                    {{ $status }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="grid grid-cols-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-1 p-4 text-center text-gray-500 dark:text-gray-400">No attendance data
                                found for the selected period.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
