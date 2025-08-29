<div>
    <div x-data="{ pageName: 'BBM Kendaraan Management' }">
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
                    <span class="text-gray-500 dark:text-gray-400">Show</span>
                    <select wire:model.live="perPage"
                        class="h-9 w-auto appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span class="text-gray-500 dark:text-gray-400">entries</span>
                </div>
                <div class="flex items-center gap-3">
                    <input type="text" placeholder="Search Driver or License Plate..."
                        wire:model.live.debounce.300ms="search"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1100px] text-sm">
                    {{-- Table Header --}}
                    <div
                        class="grid grid-cols-12 border-t border-gray-200 font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('user.name')">Driver</div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('vehicle.license_plate')">Plat Nomor</div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('status')">Status</div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800"
                            wire:click="sortBy('created_at')">Tanggal Dibuat</div>
                        <div class="col-span-4 flex items-center p-3">Dokumen Foto & Verifikasi</div>
                    </div>

                    {{-- Table Body --}}
                    @forelse ($bbmRecords as $bbm)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $bbm->user->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p class="text-gray-700 dark:text-gray-400">{{ $bbm->vehicle->license_plate ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p @class([
                                    'text-theme-xs rounded-full px-2 py-0.5 font-medium whitespace-nowrap',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-500' =>
                                        $bbm->status_bbm_kendaraan == 'proses',
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-500' =>
                                        $bbm->status_bbm_kendaraan == 'verifikasi gambar',
                                    'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-500' =>
                                        $bbm->status_bbm_kendaraan == 'revisi gambar',
                                    'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-500' =>
                                        $bbm->status_bbm_kendaraan == 'selesai',
                                ])>
                                    {{ ucfirst($bbm->status_bbm_kendaraan) }}
                                </p>
                            </div>
                            <div class="col-span-2 flex items-center border-r p-3 text-xs dark:border-gray-800">
                                {{ $bbm->created_at->format('d/m/y H:i') }}
                            </div>
                            <div class="col-span-4 flex flex-col justify-center gap-2 p-3">
                                {{-- KM Awal --}}
                                @if ($bbm->start_km_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $bbm->full_start_km_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">KM
                                            Awal</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $bbm->start_km_photo_status,
                                            'tripId' => $bbm->id,
                                            'photoType' => 'start_km_photo',
                                        ])
                                    </div>
                                @endif
                                {{-- KM Akhir --}}
                                @if ($bbm->end_km_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $bbm->full_end_km_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">KM
                                            Akhir</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $bbm->end_km_photo_status,
                                            'tripId' => $bbm->id,
                                            'photoType' => 'end_km_photo',
                                        ])
                                    </div>
                                @endif
                                {{-- Nota Pengisian --}}
                                @if ($bbm->nota_pengisian_photo_path)
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <button wire:click="openImageModal('{{ $bbm->full_nota_pengisian_photo_url }}')"
                                            class="flex-1 rounded bg-gray-100 px-2 py-1 text-left text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Nota
                                            Pengisian</button>
                                        @include('livewire.tripTable.verification-status', [
                                            'status' => $bbm->nota_pengisian_photo_status,
                                            'tripId' => $bbm->id,
                                            'photoType' => 'nota_pengisian_photo',
                                        ])
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="grid grid-cols-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-1 p-4 text-center text-gray-500 dark:text-gray-400">
                                No BBM records found.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100 py-4 px-4 dark:border-gray-800">{{ $bbmRecords->links() }}</div>

    </div>

    {{-- Modals --}}
    @include('livewire.bbmKendaraanTable.rejection-modal')
    @include('livewire.bbmKendaraanTable.image-modal')
</div>
