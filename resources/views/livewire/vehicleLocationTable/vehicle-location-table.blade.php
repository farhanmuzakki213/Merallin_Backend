<div>
    <div x-data="{ pageName: 'Verifikasi Lokasi Kendaraan' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('message'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400">Tampil</span>
                    <select wire:model.live="perPage"
                        class="h-9 w-auto rounded-lg border border-gray-300 bg-transparent text-sm focus:outline-none dark:border-gray-700">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span class="text-gray-500 dark:text-gray-400">data</span>
                </div>
                <input type="text" placeholder="Cari driver, plat nomor, atau keterangan..."
                    wire:model.live.debounce.300ms="search"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:outline-none xl:w-[300px] dark:border-gray-700">
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1100px] text-sm">
                    {{-- Table Header --}}
                    <div
                        class="grid grid-cols-12 border-t font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('created_at')">Tanggal</div>
                        <div class="col-span-5 cursor-pointer border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('vehicle_id')">Driver / Kendaraan / Keterangan</div>
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('status_vehicle_location')">Status</div>
                        <div class="col-span-3 p-3">Dokumen Foto & Verifikasi</div>
                    </div>

                    {{-- Table Body --}}
                    @forelse ($locations as $loc)
                        <div class="grid grid-cols-12 border-t text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <div class="col-span-2 border-r p-3 dark:border-gray-800">
                                {{ $loc->created_at->format('d M Y, H:i') }}</div>
                            <div class="col-span-5 border-r p-3 dark:border-gray-800">
                                <p class="font-semibold text-gray-800 dark:text-white">{{ $loc->user->name ?? 'N/A' }}
                                </p>
                                <p class="text-xs">{{ $loc->vehicle->license_plate ?? 'N/A' }}</p>
                                <p class="mt-1 text-xs italic">"{{ $loc->keterangan }}"</p>
                                <div class="mt-2 space-y-1 text-xs">
                                    @if ($loc->start_location_map_url)
                                        <a href="{{ $loc->start_location_map_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-blue-500 hover:underline">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Lihat Lokasi Awal
                                        </a>
                                    @endif
                                    @if ($loc->end_location_map_url)
                                        <a href="{{ $loc->end_location_map_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-green-500 hover:underline">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Lihat Lokasi Akhir
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-span-2 border-r p-3 dark:border-gray-800">
                                @if ($loc->status_lokasi)
                                    {{-- Tampilkan status perjalanan jika sedang aktif --}}
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">
                                            {{ ucfirst($loc->status_lokasi) }}</p>
                                    </div>
                                @else
                                    <p @class([
                                        'text-theme-xs rounded-full px-2 py-0.5 font-medium whitespace-nowrap',
                                        'bg-blue-100 text-blue-700' => $loc->status_vehicle_location == 'proses',
                                        'bg-yellow-100 text-yellow-700' =>
                                            $loc->status_vehicle_location == 'verifikasi gambar',
                                        'bg-red-100 text-red-700' =>
                                            $loc->status_vehicle_location == 'revisi gambar',
                                        'bg-green-100 text-green-700' => $loc->status_vehicle_location == 'selesai',
                                    ])>{{ ucfirst($loc->status_vehicle_location) }}</p>
                                @endif
                            </div>
                            <div class="col-span-3 flex flex-col justify-center gap-2 p-3">
                                @if ($loc->standby_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $loc->full_standby_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Foto
                                            Stanby</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $loc->standby_photo_status,
                                            'tripId' => $loc->id,
                                            'photoType' => 'standby_photo',
                                        ])
                                    </div>
                                @endif
                                @if ($loc->start_km_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $loc->full_start_km_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">KM
                                            Awal</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $loc->start_km_photo_status,
                                            'tripId' => $loc->id,
                                            'photoType' => 'start_km_photo',
                                        ])
                                    </div>
                                @endif
                                @if ($loc->end_km_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $loc->full_end_km_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">KM
                                            Akhir</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $loc->end_km_photo_status,
                                            'tripId' => $loc->id,
                                            'photoType' => 'end_km_photo',
                                        ])
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="border-t p-4 text-center dark:border-gray-800">Tidak ada data ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="border-t py-4 px-4 dark:border-gray-800">{{ $locations->links() }}</div>
    </div>

    {{-- Modals --}}
    @if ($showImageModal)
        @include('livewire.tripTable.trip-image-modal')
    @endif
    @if ($showRejectionModal)
        @include('livewire.bbmKendaraanTable.rejection-modal') {{-- Re-use modal, it's generic enough --}}
    @endif
</div>
