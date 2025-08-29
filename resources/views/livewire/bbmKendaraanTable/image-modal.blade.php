@if ($showImageModal)
    <div x-data="{ show: @entangle('showImageModal') }" x-show="show" x-transition class="fixed inset-0 z-[999999] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>
        <div class="relative w-auto max-w-4xl max-h-[90vh]">
            <button @click="$wire.closeImageModal()" class="absolute -top-4 -right-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-white hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <img src="{{ $imageUrl }}" alt="Detail Foto" class="h-auto w-full rounded-lg object-contain max-h-[90vh]">
        </div>
    </div>
@endif
