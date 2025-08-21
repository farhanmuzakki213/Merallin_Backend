<div x-data="{ show: @entangle('showDeliveryLetterModal') }" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[999999] flex items-center justify-center bg-black bg-opacity-80 p-4" x-cloak>

    {{-- Tombol Tutup Utama --}}
    <button wire:click="closeDeliveryLetterModal"
        class="absolute top-4 right-4 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-gray-800 text-white hover:bg-gray-600 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <div class="flex h-full w-full max-w-7xl items-center justify-center gap-4">
        <div class="relative flex h-full w-1/2 flex-col items-center justify-center">
            <h3
                class="absolute top-0 left-1/2 -translate-x-1/2 rounded-b-lg bg-white/10 px-4 py-2 text-lg font-semibold text-white backdrop-blur-sm">
                Surat Jalan Awal (Initial)
            </h3>
            @if (!empty($initialLetters))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($initialLetters[$currentInitialIndex]) }}"
                    alt="Initial Letter {{ $currentInitialIndex + 1 }}"
                    class="h-auto max-h-full w-auto rounded-lg object-contain">

                {{-- Navigasi Kiri --}}
                @if ($currentInitialIndex > 0)
                    <button wire:click="previousInitialLetter"
                        class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                @endif
                @if ($currentInitialIndex < count($initialLetters) - 1)
                    <button wire:click="nextInitialLetter"
                        class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                @endif

                {{-- Counter Kiri --}}
                <div
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                    {{ $currentInitialIndex + 1 }} / {{ count($initialLetters) }}
                </div>
            @else
                <div class="flex h-full items-center justify-center rounded-lg bg-gray-900/50 text-white">Tidak ada
                    gambar.</div>
            @endif
        </div>

        <div class="relative flex h-full w-1/2 flex-col items-center justify-center">
            <h3
                class="absolute top-0 left-1/2 -translate-x-1/2 rounded-b-lg bg-white/10 px-4 py-2 text-lg font-semibold text-white backdrop-blur-sm">
                Surat Jalan Akhir (Final)
            </h3>
            @if (!empty($finalLetters))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($finalLetters[$currentFinalIndex]) }}"
                    alt="Final Letter {{ $currentFinalIndex + 1 }}"
                    class="h-auto max-h-full w-auto rounded-lg object-contain">

                {{-- Navigasi Kanan --}}
                @if ($currentFinalIndex > 0)
                    <button wire:click="previousFinalLetter"
                        class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                @endif
                @if ($currentFinalIndex < count($finalLetters) - 1)
                    <button wire:click="nextFinalLetter"
                        class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black/75">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                @endif

                {{-- Counter Kanan --}}
                <div
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                    {{ $currentFinalIndex + 1 }} / {{ count($finalLetters) }}
                </div>
            @else
                <div class="flex h-full items-center justify-center rounded-lg bg-gray-900/50 text-white">Tidak ada
                    gambar.</div>
            @endif
        </div>
    </div>
</div>
