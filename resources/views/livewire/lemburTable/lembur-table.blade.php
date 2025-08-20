<div>
    {{-- Breadcrumb --}}
    <div x-data="{ pageName: 'Data Lembur Karyawan' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Notifikasi Sukses --}}
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-emerald-100 p-4 text-sm text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-500" role="alert">
            {{ session('message') }}
        </div>
    @endif

    {{-- Kontainer Utama Tabel --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Header Kontrol --}}
            <div class="mb-4 flex flex-col gap-3 px-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400">Show</span>
                    <select wire:model.live="perPage" class="h-9 w-auto appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span class="text-gray-500 dark:text-gray-400">entries</span>
                </div>
                <input type="text" placeholder="Cari berdasarkan nama, department, keterangan..." wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm placeholder:text-gray-400 focus:outline-hidden sm:w-[350px] dark:border-gray-700 dark:bg-gray-900">
            </div>

            {{-- Wrapper Tabel --}}
            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1200px] text-sm">
                    {{-- Header Tabel --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800" wire:click="sortBy('user.name')">Karyawan</div>
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800" wire:click="sortBy('tanggal_lembur')">Tanggal</div>
                        <div class="col-span-1 border-r p-3 dark:border-gray-800">Jadwal</div>
                        <div class="col-span-3 border-r p-3 dark:border-gray-800">Keterangan</div>
                        <div class="col-span-3 border-r p-3 dark:border-gray-800">Status Persetujuan</div>
                        <div class="col-span-1 p-3">Aksi</div>
                    </div>

                    {{-- Body Tabel --}}
                    @forelse ($lemburs as $lembur)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            {{-- Karyawan --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <div>
                                    <p class="block font-medium text-gray-800 dark:text-white/90">{{ $lembur->user->name ?? 'N/A' }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lembur->department ?? '' }}</span>
                                </div>
                            </div>
                            {{-- Tanggal --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <div>
                                    <p class="text-gray-800 dark:text-white/90">{{ \Carbon\Carbon::parse($lembur->tanggal_lembur)->isoFormat('D MMM Y') }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lembur->jenis_hari }}</span>
                                </div>
                            </div>
                            {{-- Jadwal --}}
                            <div class="col-span-1 flex items-center border-r p-3 dark:border-gray-800">
                                {{ \Carbon\Carbon::parse($lembur->mulai_jam_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lembur->selesai_jam_lembur)->format('H:i') }}
                            </div>
                            {{-- Keterangan --}}
                            <div class="col-span-3 flex items-center border-r p-3 dark:border-gray-800 whitespace-normal">
                                {{ $lembur->keterangan_lembur ?? '-' }}
                            </div>
                            {{-- Status Persetujuan --}}
                            <div class="col-span-3 space-y-2 border-r p-3 dark:border-gray-800">
                                @include('livewire.lemburTable.lembur-status', ['lembur' => $lembur])
                            </div>
                            {{-- Aksi --}}
                            <div class="col-span-1 flex items-center justify-center p-3">
                                <button wire:click="showPdfPreview({{ $lembur->id }})" class="flex h-8 w-8 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="Cetak SPKL">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-12 border-t border-gray-100 p-5 text-center text-gray-500 dark:border-gray-800">
                            Tidak ada data lembur yang ditemukan.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Paginasi --}}
            <div class="border-t border-gray-100 py-4 px-4 dark:border-gray-800">
                {{ $lemburs->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Aksi --}}
    @include('livewire.lemburTable.confirm-modal')

    {{-- Modal Pratinjau PDF --}}
    @include('livewire.lemburTable.pdf-preview-modal')
</div>
