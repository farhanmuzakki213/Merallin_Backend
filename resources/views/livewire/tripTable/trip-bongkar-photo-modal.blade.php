<div x-data="{ show: @entangle('showBongkarPhotoModal') }"
     x-show="show"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-75 p-4"
     x-cloak
     @keydown.escape.window="$wire.closeBongkarPhotoModal()"
     @keydown.arrow-right.window="$wire.nextBongkarPhoto()"
     @keydown.arrow-left.window="$wire.previousBongkarPhoto()">

    <div class="relative w-auto max-w-5xl max-h-[90vh]">
        {{-- Tombol Tutup (Close Button) --}}
        <button @click="$wire.closeBongkarPhotoModal()"
            class="absolute -top-4 -right-4 z-50 flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-white hover:bg-gray-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        {{-- Konten Galeri --}}
        <div class="relative flex h-full items-center justify-center"
             wire:loading.class="animate-pulse"
             wire:target="nextBongkarPhoto, previousBongkarPhoto">

            {{-- Tombol Previous --}}
            @if ($currentBongkarPhotoIndex > 0)
                <button wire:click="previousBongkarPhoto"
                    class="absolute left-0 top-1/2 z-40 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75 focus:outline-none md:-left-12">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @endif

            {{-- Tampilan Gambar Utama --}}
            @if (!empty($bongkarPhotos) && isset($bongkarPhotos[$currentBongkarPhotoIndex]))
                <img src="{{ Storage::url($bongkarPhotos[$currentBongkarPhotoIndex]) }}"
                    alt="Foto Bongkar {{ $currentBongkarPhotoIndex + 1 }}"
                    class="h-auto w-full rounded-lg object-contain max-h-[90vh]">
            @endif

            {{-- Tombol Next --}}
            @if ($currentBongkarPhotoIndex < count($bongkarPhotos) - 1)
                <button wire:click="nextBongkarPhoto"
                    class="absolute right-0 top-1/2 z-40 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75 focus:outline-none md:-right-12">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Informasi Jumlah Foto --}}
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-3 py-1 text-sm text-white">
            <span>
                Foto {{ $currentBongkarPhotoIndex + 1 }} dari {{ count($bongkarPhotos) }}
            </span>
        </div>
    </div>
</div>
