<div>
    {{-- Breadcrumb --}}
    <div x-data="{ pageName: 'Izin Karyawan' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Kontainer Utama Tabel --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Header Kontrol: Search & Per Page --}}
            <div class="mb-4 flex flex-col gap-3 px-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Kontrol Per Page --}}
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

                {{-- Input Pencarian --}}
                <input type="text" placeholder="Cari berdasarkan nama, jenis izin, atau alasan..."
                    wire:model.live.debounce.300ms="search"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm placeholder:text-gray-400 focus:outline-hidden sm:w-[350px] dark:border-gray-700 dark:bg-gray-900">
            </div>

            {{-- Wrapper Tabel agar Responsif --}}
            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[1000px] text-sm">
                    {{-- Header Tabel --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 font-medium text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <div class="col-span-3 cursor-pointer border-r p-3 dark:border-gray-800" wire:click="sortBy('user.name')">Karyawan</div>
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800" wire:click="sortBy('jenis_izin')">Jenis Izin</div>
                        <div class="col-span-2 cursor-pointer border-r p-3 dark:border-gray-800" wire:click="sortBy('tanggal_mulai')">Tanggal</div>
                        <div class="col-span-4 border-r p-3 dark:border-gray-800">Alasan</div>
                        <div class="col-span-1 p-3">Bukti</div>
                    </div>

                    {{-- Body Tabel --}}
                    @forelse ($izins as $izin)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            {{-- Kolom Karyawan --}}
                            <div class="col-span-3 flex items-center border-r p-3 dark:border-gray-800">
                                <div>
                                    <p class="block font-medium text-gray-800 dark:text-white/90">{{ $izin->user->name ?? 'N/A' }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $izin->user->email ?? '' }}</span>
                                </div>
                            </div>
                            {{-- Kolom Jenis Izin --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                <p @class([
                                    'text-theme-xs rounded-full px-2 py-0.5 font-medium',
                                    'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-500' => $izin->jenis_izin == 'Kepentingan Keluarga',
                                    'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-500' => $izin->jenis_izin == 'Sakit',
                                ])>
                                    {{ $izin->jenis_izin }}
                                </p>
                            </div>
                            {{-- Kolom Tanggal --}}
                            <div class="col-span-2 flex items-center border-r p-3 dark:border-gray-800">
                                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}
                            </div>
                            {{-- Kolom Alasan --}}
                            <div class="col-span-4 flex items-center border-r p-3 dark:border-gray-800 whitespace-normal">
                                {{ $izin->alasan ?? '-' }}
                            </div>
                            {{-- Kolom Bukti --}}
                            <div class="col-span-1 flex items-center justify-center p-3">
                                @if ($izin->full_url_bukti)
                                    <button wire:click="openImageModal('{{ $izin->full_url_bukti }}', '{{ $izin->jenis_izin }}')"
                                        class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                                        Lihat
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-12 p-5 text-center text-gray-500">
                            Tidak ada data izin yang ditemukan.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Paginasi --}}
            <div class="border-t border-gray-100 py-4 px-4 dark:border-gray-800">
                {{ $izins->links() }}
            </div>
        </div>
    </div>

    {{-- Modal untuk Menampilkan Gambar --}}
    <div x-data="{ show: @entangle('showImageModal').live }" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>

        <div @click.outside="$wire.closeImageModal()" class="relative w-auto max-w-3xl max-h-[90vh]">
            {{-- Tombol Tutup --}}
            <button @click="$wire.closeImageModal()"
                class="absolute -top-3 -right-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-gray-800 text-white hover:bg-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            {{-- Konten Modal --}}
            <div class="rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="$wire.imageTitle"></h3>
                </div>
                <div class="p-4">
                    <img :src="$wire.imageUrl" alt="Bukti Izin" class="h-auto w-full rounded-lg object-contain max-h-[75vh]">
                </div>
            </div>
        </div>
    </div>
</div>
