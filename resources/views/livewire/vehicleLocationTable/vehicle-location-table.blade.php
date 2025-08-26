<div>
    <div x-data="{ pageName: 'Lokasi Kendaraan' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('message'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Kontrol Entri --}}
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400"> Tampil </span>
                    <select wire:model.live="perPage" class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                    </select>
                    <span class="text-gray-500 dark:text-gray-400"> data </span>
                </div>
                {{-- Tombol & Pencarian --}}
                <div class="flex items-center gap-3">
                    <input type="text" placeholder="Cari..." wire:model.live.debounce.300ms="search" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900">
                    <button wire:click="openModal()" class="flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                        Tambah Lokasi
                    </button>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1100px] text-sm">
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800" wire:click="sortBy('reported_at')"><p class="font-medium">Tanggal Lapor</p></div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800" wire:click="sortBy('vehicle_id')"><p class="font-medium">Kendaraan / Driver</p></div>
                        <div class="col-span-3 flex cursor-pointer items-center border-r p-3 dark:border-gray-800" wire:click="sortBy('location')"><p class="font-medium">Lokasi</p></div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800" wire:click="sortBy('event_type')"><p class="font-medium">Keterangan Acara</p></div>
                        <div class="col-span-2 flex cursor-pointer items-center border-r p-3 dark:border-gray-800" wire:click="sortBy('remarks')"><p class="font-medium">Catatan</p></div>
                        <div class="col-span-1 flex items-center p-3"><p class="font-medium">Aksi</p></div>
                    </div>
                    {{-- Table Body --}}
                    @forelse ($locations as $loc)
                        <div class="grid grid-cols-12 border-t border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            {{-- Tanggal Lapor --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p>{{ $loc->reported_at->format('d M Y, H:i') }}</p>
                            </div>
                            {{-- Kendaraan / Driver (Digabung) --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white/90">{{ $loc->vehicle->license_plate ?? 'N/A' }}</p>
                                    <span class="text-xs">{{ $loc->user->name ?? '-' }}</span>
                                </div>
                            </div>
                            {{-- Lokasi --}}
                            <div class="col-span-3 flex items-center border-r p-3 dark:border-gray-800">
                                <p>{{ $loc->location }}</p>
                            </div>
                            {{-- Keterangan Acara --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <div>
                                    <p>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $loc->event_type)) }}</p>
                                    @if($loc->trip)
                                    <span class="text-xs">{{ $loc->trip->project_name }}</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Catatan (Baru) --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p>{{ $loc->remarks ?? '-' }}</p>
                            </div>
                            {{-- Aksi --}}
                            <div class="col-span-1 flex items-center px-4 py-3">
                                <div class="flex w-full items-center gap-2">
                                    <button wire:click="edit({{ $loc->id }})" class="text-gray-500 hover:text-gray-800">
                                        {{-- SVG Edit Icon --}}
                                        <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"><path d="M17.0911 3.53206C16.2124 2.65338 14.7878 2.65338 13.9091 3.53206L5.6074 11.8337C5.29899 12.1421 5.08687 12.5335 4.99684 12.9603L4.26177 16.445C4.20943 16.6931 4.286 16.9508 4.46529 17.1301C4.64458 17.3094 4.90232 17.3859 5.15042 17.3336L8.63507 16.5985C9.06184 16.5085 9.45324 16.2964 9.76165 15.988L18.0633 7.68631C18.942 6.80763 18.942 5.38301 18.0633 4.50433L17.0911 3.53206Z" fill="currentColor"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $loc->id }})" wire:confirm="Anda yakin ingin menghapus data ini?" class="text-gray-500 hover:text-red-500">
                                        {{-- SVG Delete Icon --}}
                                        <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"><path d="M7.04142 4.29199C7.04142 3.04935 8.04878 2.04199 9.29142 2.04199H11.7081C12.9507 2.04199 13.9581 3.04935 13.9581 4.29199V4.54199H17.166C17.5802 4.54199 17.916 4.87778 17.916 5.29199C17.916 5.70621 17.5802 6.04199 17.166 6.04199H3.8335C3.41928 6.04199 3.0835 5.70621 3.0835 5.29199C3.0835 4.87778 3.41928 4.54199 3.8335 4.54199H7.04142V4.29199ZM8.54142 4.54199H12.4581V4.29199C12.4581 3.87778 12.1223 3.54199 11.7081 3.54199H9.29142C8.87721 3.54199 8.54142 3.87778 8.54142 4.29199V4.54199Z" fill="currentColor"/><path d="M5.62516 8.74687V16.7087C5.62516 17.1229 5.96095 17.4587 6.37516 17.4587H14.6252C15.0394 17.4587 15.3752 17.1229 15.3752 16.7087V8.74687H5.62516Z" fill="currentColor"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="grid grid-cols-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-1 p-4 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data lokasi kendaraan.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-gray-100 py-4 px-4 dark:border-gray-800">
                {{ $locations->links() }}
            </div>
        </div>
    </div>

    @if ($showModal)
        @include('livewire.vehicleLocationTable.vehicle-location-modal')
    @endif
</div>
