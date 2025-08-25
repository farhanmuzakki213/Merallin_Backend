<div>
    {{-- Breadcrumb --}}
    <div x-data="{ pageName: 'Dashboard' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Grid Utama Dashboard --}}
    <div class="grid grid-cols-12 gap-4 md:gap-6">

        {{-- =============================================================== --}}
        {{-- Baris 1: Statistik Utama (Menggunakan gaya dari metric-group-01) --}}
        {{-- =============================================================== --}}
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 md:gap-6">
            {{-- Card Total Karyawan --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24">
                        <path
                            d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Karyawan</span>
                    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $totalKaryawan }}</h4>
                </div>
            </div>
            {{-- Card Total Driver --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24">
                        <path
                            d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Driver</span>
                    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $totalDriver }}</h4>
                </div>
            </div>
            {{-- Card Perjalanan Aktif --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24">
                        <path d="M8 11h3v10h2V11h3l-4-4-4 4zM4 3v2h16V3H4z" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Perjalanan Aktif</span>
                    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $perjalananAktif }}
                    </h4>
                </div>
            </div>
            {{-- Card Karyawan Hadir Hari Ini --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24">
                        <path
                            d="M12 5.9c1.16 0 2.1.94 2.1 2.1s-.94 2.1-2.1 2.1S9.9 9.16 9.9 8s.94-2.1 2.1-2.1m0 9c2.97 0 6.1 1.46 6.1 2.1v1.1H5.9V17c0-.64 3.13-2.1 6.1-2.1M12 4C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 9c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Hadir Hari Ini</span>
                    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                        {{ $karyawanHadirHariIni }}</h4>
                </div>
            </div>
        </div>


        {{-- =============================================================== --}}
        {{-- Baris 2: Grafik (Menggunakan gaya dari chart-01 & chart-02) --}}
        {{-- =============================================================== --}}
        <div class="col-span-12 xl:col-span-8">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                wire:ignore x-data x-init="window.initTripChart({{ json_encode($initialTripChart) }});"
                x-on:trip-chart-updated.document="window.updateTripChart($event.detail.data)">
                <div class="px-5 pt-5 sm:px-6 sm:pt-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Statistik Perjalanan
                        </h3>
                        <div class="relative w-full sm:w-56">
                            {{-- Pastikan ID ini sesuai dengan yang dipanggil di app.js --}}
                            <input id="trip-chart-datepicker"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-[34px] pr-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Filter berdasarkan tanggal..." readonly="readonly" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 fill-gray-700 dark:fill-gray-400" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div class="min-w-[650px] pl-2 xl:min-w-full">
                        <div id="tripStatusBarChart" class="h-full min-w-[650px] pl-2 xl:min-w-full"></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-span-12 xl:col-span-4">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Distribusi Role</h3>
                <div id="userRoleDonutChart" data-series='{{ json_encode($userRoleChart['data']) }}'
                    data-labels='{{ json_encode($userRoleChart['labels']) }}'>
                </div>
            </div>
        </div>


        {{-- ====================================================================== --}}
        {{-- Baris 3: Aktivitas Terkini (Menggunakan gaya dari table-01 & table-06) --}}
        {{-- ====================================================================== --}}
        <div class="col-span-12 xl:col-span-7">
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Aktivitas Absensi Terkini</h3>
                <div class="w-full overflow-x-auto">
                    @forelse ($absensiTerkini as $absensi)
                        <div
                            class="flex items-center justify-between border-t border-gray-100 py-3.5 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full">
                                    <img src="{{ $absensi->user->profile_photo_url }}" alt="User">
                                </div>
                                <div>
                                    <span
                                        class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $absensi->user->name }}</span>
                                    <span
                                        class="block text-gray-500 text-theme-xs dark:text-gray-400">{{ \Carbon\Carbon::parse($absensi->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>
                            <p @class([
                                'rounded-full px-2 py-0.5 text-theme-xs font-medium',
                                'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' =>
                                    $absensi->tipe_absensi == 'clock_in',
                                'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' =>
                                    $absensi->tipe_absensi == 'clock_out',
                            ])>
                                {{ $absensi->tipe_absensi == 'clock_in' ? 'Clock In' : 'Clock Out' }}
                            </p>
                        </div>
                    @empty
                        <div class="py-4 text-center text-gray-500">Belum ada aktivitas absensi.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-5">
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Pengajuan Terbaru</h3>
                <div class="w-full overflow-x-auto">
                    @if ($izinTerkini->isEmpty() && $lemburTerkini->isEmpty())
                        <div class="py-4 text-center text-gray-500">Tidak ada pengajuan terbaru.</div>
                    @endif

                    @foreach ($izinTerkini as $izin)
                        <div
                            class="flex items-center justify-between border-t border-gray-100 py-3.5 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full">
                                    <img src="{{ $izin->user->profile_photo_url }}" alt="User">
                                </div>
                                <div>
                                    <span
                                        class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $izin->user->name }}</span>
                                    <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Mengajukan
                                        Izin</span>
                                </div>
                            </div>
                            <p
                                class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-orange-400">
                                {{ $izin->jenis_izin }}
                            </p>
                        </div>
                    @endforeach

                    @foreach ($lemburTerkini as $lembur)
                        <div
                            class="flex items-center justify-between border-t border-gray-100 py-3.5 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full">
                                    <img src="{{ $lembur->user->profile_photo_url }}" alt="User">
                                </div>
                                <div>
                                    <span
                                        class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $lembur->user->name }}</span>
                                    <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Mengajukan
                                        Lembur</span>
                                </div>
                            </div>
                            <p
                                class="rounded-full bg-blue-50 px-2 py-0.5 text-theme-xs font-medium text-blue-700 dark:bg-blue-500/15 dark:text-blue-500">
                                {{ $lembur->department }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ====================================================================== --}}
        {{-- Baris 4: Peringkat Driver Teratas (Tabel Baru) --}}
        {{-- ====================================================================== --}}
        <div class="col-span-12">
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Peringkat Driver Teratas (Bulan
                    Ini)</h3>
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    No
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Nama Driver
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Trip Perusahaan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Trip Driver
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Trip
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            @forelse ($topDrivers as $driver)
                                <tr>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{ $driver->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{ $driver->trip_perusahaan }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{ $driver->trip_driver }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $driver->total_trip }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Belum ada data trip selesai untuk bulan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
