@if ($showRejectionModal)
    <div class="fixed inset-0 z-[999999] flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-boxdark">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Alasan Penolakan</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Mohon berikan alasan yang jelas mengapa foto ini ditolak.</p>
            <div class="mt-4">
                <textarea wire:model="rejectionReason" rows="4" class="w-full rounded-lg border border-gray-300 p-2 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-boxdark-2"></textarea>
                @error('rejectionReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <button wire:click="closeRejectionModal" type="button" class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700">Batal</button>
                <button wire:click="rejectPhoto" type="button" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Tolak Foto</button>
            </div>
        </div>
    </div>
@endif
