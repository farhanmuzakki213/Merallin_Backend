<div>
    <div x-data="{ pageName: 'Trip Management' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('message'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
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
                <div class="flex items-center gap-3">
                    <input type="text" placeholder="Search..." wire:model.live.debounce.300ms="search"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <button wire:click="openModal()"
                        class="flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                        Create Trip
                    </button>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1102px]">
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                        <div
                            class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full cursor-pointer items-center justify-between"
                                wire:click="sortBy('project_name')">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Project Name</p>
                            </div>
                        </div>
                        <div
                            class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full cursor-pointer items-center justify-between"
                                wire:click="sortBy('origin')">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Origin</p>
                            </div>
                        </div>
                        <div
                            class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full cursor-pointer items-center justify-between"
                                wire:click="sortBy('destination')">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Destination</p>
                            </div>
                        </div>
                        <div
                            class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Driver</p>
                        </div>
                        <div
                            class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                            <div class="flex w-full cursor-pointer items-center justify-between"
                                wire:click="sortBy('status_trip')">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                            </div>
                        </div>
                        <div class="col-span-2 flex items-center px-4 py-3">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Action</p>
                        </div>
                    </div>
                    {{-- Table Body --}}
                    @forelse ($trips as $trip)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            <div
                                class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $trip->project_name }}</p>
                            </div>
                            <div
                                class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">{{ $trip->origin }}</p>
                            </div>
                            <div
                                class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">{{ $trip->destination }}</p>
                            </div>
                            <div
                                class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">
                                    {{ $trip->user->name ?? 'Not Assigned' }}</p>
                            </div>
                            <div
                                class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p @class([
                                    'text-theme-xs rounded-full px-2 py-0.5 font-medium',
                                    'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-orange-400' =>
                                        $trip->status_trip == 'tersedia',
                                    'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-500' =>
                                        $trip->status_trip == 'proses',
                                    'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' =>
                                        $trip->status_trip == 'selesai',
                                ])>
                                    {{ ucfirst($trip->status_trip) }}
                                </p>
                            </div>
                            <div class="col-span-2 flex items-center px-4 py-3">
                                <div class="flex w-full items-center gap-2">
                                    @if ($trip->status_trip == 'tersedia')
                                        <button wire:click="edit({{ $trip->id }})"
                                            class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90">
                                            <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M17.0911 3.53206C16.2124 2.65338 14.7878 2.65338 13.9091 3.53206L5.6074 11.8337C5.29899 12.1421 5.08687 12.5335 4.99684 12.9603L4.26177 16.445C4.20943 16.6931 4.286 16.9508 4.46529 17.1301C4.64458 17.3094 4.90232 17.3859 5.15042 17.3336L8.63507 16.5985C9.06184 16.5085 9.45324 16.2964 9.76165 15.988L18.0633 7.68631C18.942 6.80763 18.942 5.38301 18.0633 4.50433L17.0911 3.53206ZM14.9697 4.59272C15.2626 4.29982 15.7375 4.29982 16.0304 4.59272L17.0027 5.56499C17.2956 5.85788 17.2956 6.33276 17.0027 6.62565L16.1043 7.52402L14.0714 5.49109L14.9697 4.59272ZM13.0107 6.55175L6.66806 12.8944C6.56526 12.9972 6.49455 13.1277 6.46454 13.2699L5.96704 15.6283L8.32547 15.1308C8.46772 15.1008 8.59819 15.0301 8.70099 14.9273L15.0436 8.58468L13.0107 6.55175Z"
                                                    fill=""></path>
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $trip->id }})"
                                            wire:confirm="Are you sure you want to delete this trip?"
                                            class="hover:text-error-500 dark:hover:text-error-500 text-gray-500 dark:text-gray-400">
                                            <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M7.04142 4.29199C7.04142 3.04935 8.04878 2.04199 9.29142 2.04199H11.7081C12.9507 2.04199 13.9581 3.04935 13.9581 4.29199V4.54199H16.1252H17.166C17.5802 4.54199 17.916 4.87778 17.916 5.29199C17.916 5.70621 17.5802 6.04199 17.166 6.04199H16.8752V8.74687V13.7469V16.7087C16.8752 17.9513 15.8678 18.9587 14.6252 18.9587H6.37516C5.13252 18.9587 4.12516 17.9513 4.12516 16.7087V13.7469V8.74687V6.04199H3.8335C3.41928 6.04199 3.0835 5.70621 3.0835 5.29199C3.0835 4.87778 3.41928 4.54199 3.8335 4.54199H4.87516H7.04142V4.29199ZM15.3752 13.7469V8.74687V6.04199H13.9581H13.2081H7.79142H7.04142H5.62516V8.74687V13.7469V16.7087C5.62516 17.1229 5.96095 17.4587 6.37516 17.4587H14.6252C15.0394 17.4587 15.3752 17.1229 15.3752 16.7087V13.7469ZM8.54142 4.54199H12.4581V4.29199C12.4581 3.87778 12.1223 3.54199 11.7081 3.54199H9.29142C8.87721 3.54199 8.54142 3.87778 8.54142 4.29199V4.54199ZM8.8335 8.50033C9.24771 8.50033 9.5835 8.83611 9.5835 9.25033V14.2503C9.5835 14.6645 9.24771 15.0003 8.8335 15.0003C8.41928 15.0003 8.0835 14.6645 8.0835 14.2503V9.25033C8.0835 8.83611 8.41928 8.50033 8.8335 8.50033ZM12.9168 9.25033C12.9168 8.83611 12.581 8.50033 12.1668 8.50033C11.7526 8.50033 11.4168 8.83611 11.4168 9.25033V14.2503C11.4168 14.6645 11.7526 15.0003 12.1668 15.0003C12.581 15.0003 12.9168 14.6645 12.9168 14.2503V9.25033Z"
                                                    fill=""></path>
                                            </svg>
                                        </button>
                                    @else
                                        <p
                                            class="text-theme-xs rounded-full px-2 py-0.5 font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-orange-400">
                                            Tugas Sudah Diambil Driver
                                        </p>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="grid grid-cols-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-1 p-4 text-center text-gray-500 dark:text-gray-400">
                                No trips found.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
                {{ $trips->links() }}
            </div>
        </div>
    </div>

    <div class="mt-12">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h3 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white/90">Tabel Detail Trip</h3>
            <div
                class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3"><span
                            class="text-gray-500 dark:text-gray-400">Show</span><select
                            wire:model.live="detailPerPage"
                            class="h-9 w-auto appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value-="25">25</option>
                        </select><span class="text-gray-500 dark:text-gray-400">entries</span></div>
                    <input type="text" placeholder="Search in details..."
                        wire:model.live.debounce.300ms="detailSearch"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900">
                </div>

                <div class="max-w-full overflow-x-auto">
                    <div class="min-w-[1100px] text-sm">
                        {{-- Header Tabel Detail (DIPERBARUI) --}}
                        <div
                            class="grid grid-cols-12 border-t border-gray-200 font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <div class="col-span-2 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('user.name')">Driver / Plat Nomor</div>
                            <div class="col-span-3 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('project_name')">Project</div>
                            <div class="col-span-1 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('start_km')">KM Awal </div>
                            <div class="col-span-1 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('end_km')">KM Akhir</div>
                            <div class="col-span-2 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('status_lokasi')">Status</div>
                            <div class="col-span-1 flex items-center cursor-pointer border-r p-3 dark:border-gray-800"
                                wire:click="sortByDetail('created_at')">Tanggal</div>
                            <div class="col-span-1 flex items-center p-3">Dokumen Foto</div>
                        </div>

                        {{-- Body Tabel Detail (DIPERBARUI) --}}
                        @forelse ($detailTrips as $trip)
                            <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                                {{-- Kolom Driver & Plat Nomor Gabungan --}}
                                <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                    <div>
                                        <p class="text-theme-sm block font-medium text-gray-800 dark:text-white/90">
                                            {{ $trip->user->name ?? 'N/A' }}</p>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400">{{ $trip->license_plate ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-span-3 flex items-center border-r p-3 dark:border-gray-800">
                                    {{ $trip->project_name }}</div>
                                {{-- KM Awal dengan Link Pop-up --}}
                                <div class="col-span-1 flex items-center border-r p-3 dark:border-gray-800">
                                    @if ($trip->start_km_photo_path)
                                        <button
                                            wire:click="openImageModal('{{ \Illuminate\Support\Facades\Storage::url($trip->start_km_photo_path) }}')"
                                            class="font-medium text-brand-500 hover:underline">
                                            {{ $trip->start_km ? number_format($trip->start_km) . ' km' : 'Lihat Foto' }}
                                        </button>
                                    @else
                                        <span>{{ $trip->start_km ? number_format($trip->start_km) . ' km' : 'N/A' }}</span>
                                    @endif
                                </div>
                                {{-- KM Akhir dengan Link Pop-up --}}
                                <div class="col-span-1 flex items-center border-r p-3 dark:border-gray-800">
                                    @if ($trip->end_km_photo_path)
                                        <button
                                            wire:click="openImageModal('{{ \Illuminate\Support\Facades\Storage::url($trip->end_km_photo_path) }}')"
                                            class="font-medium text-brand-500 hover:underline">
                                            {{ $trip->end_km ? number_format($trip->end_km) . ' km' : 'Lihat Foto' }}
                                        </button>
                                    @else
                                        <span>{{ $trip->end_km ? number_format($trip->end_km) . ' km' : 'N/A' }}</span>
                                    @endif
                                </div>
                                {{-- Status Detail Gabungan --}}
                                <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                    @if (($trip->status_lokasi && $trip->status_muatan) == null)
                                        {{ ucfirst($trip->status_trip) }}
                                    @else
                                        <div>
                                            <p
                                                class="text-theme-sm block font-medium text-gray-800 dark:text-white/90">
                                                {{ ucfirst($trip->status_lokasi) ?? 'Belum ada info lokasi' }}</p>
                                            <span
                                                class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($trip->status_muatan) ?? 'Belum ada info muatan' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-span-1 flex items-center border-r p-3 dark:border-gray-800">
                                    {{ ($trip->created_at ?? $trip->updated_at)?->format('d/m/y H:i') ?? 'N/A' }}
                                </div>
                                {{-- Tombol Dokumen Foto Lainnya --}}
                                <div class="col-span-1 flex items-center flex-wrap items-center gap-2 p-3">
                                    @php $other_photos = ['Muat' => $trip->muat_photo_path, 'Bongkar' => $trip->bongkar_photo_path, 'SJ' => $trip->delivery_letter_path]; @endphp
                                    @foreach ($other_photos as $label => $path)
                                        @if ($path)
                                            <button
                                                wire:click="openImageModal('{{ \Illuminate\Support\Facades\Storage::url($path) }}')"
                                                class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">{{ $label }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="col-span-12 p-4 text-center text-gray-500">No details found.</div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-gray-100 py-4 px-4 dark:border-gray-800">{{ $detailTrips->links() }}</div>
            </div>
        </div>
    </div>

    @if ($showModal)
        @include('livewire.tripTable.trip-edit-modal')
    @endif

    @if ($showImageModal)
        @include('livewire.tripTable.trip-image-modal')
    @endif
</div>
