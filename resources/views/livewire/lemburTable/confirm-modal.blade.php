<div x-data="{ show: @entangle('showConfirmModal').live }" x-show="show" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>

    <div @click.outside="$wire.cancelConfirmation()" class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Tindakan</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" x-text="$wire.confirmMessage"></p>

        {{-- Form Fields --}}
        <div class="mt-4 space-y-4">
            {{-- Alasan Penolakan --}}
            @if ($confirmAction === 'reject')
                <div>
                    <label for="alasan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Penolakan</label>
                    <textarea wire:model="alasan" id="alasan" rows="3"
                        class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                        placeholder=" Berikan alasan yang jelas..."></textarea>
                    @error('alasan') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- Upload File Persetujuan (Hanya untuk Manajer/Direksi) --}}
            @if ($confirmAction === 'approve' && ($confirmLevel === 'manajer' || $confirmLevel === 'direksi'))
                <div x-data="{ isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">

                    <label for="file_path" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload SPKL Bertanda Tangan (PDF)</label>
                    <input wire:model="file_path" type="file" id="file_path" accept=".pdf"
                           class="w-full rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:bg-gray-100 file:font-semibold hover:file:bg-gray-200 dark:border-gray-700 dark:bg-boxdark-2 dark:file:bg-gray-700 dark:file:text-gray-300">

                    <!-- Progress Bar -->
                    <div x-show="isUploading" class="mt-2 w-full bg-gray-200 rounded-full dark:bg-gray-700">
                        <div class="bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full" :style="`width: ${progress}%`" x-text="`${progress}%`"></div>
                    </div>

                    @error('file_path') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <button @click="$wire.cancelConfirmation()" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Batal
            </button>
            <button
                wire:click="{{ $confirmLevel === 'admin' ? 'processAdminAction' : 'processAction' }}"
                wire:loading.attr="disabled"
                type="button"
                :class="{
                    'bg-emerald-600 hover:bg-emerald-700': $wire.confirmAction === 'approve',
                    'bg-red-600 hover:bg-red-700': $wire.confirmAction === 'reject'
                }"
                class="inline-flex justify-center rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none disabled:opacity-50">
                <span wire:loading.remove wire:target="{{ $confirmLevel === 'admin' ? 'processAdminAction' : 'processAction' }}">
                    Ya, Lanjutkan
                </span>
                <span wire:loading wire:target="{{ $confirmLevel === 'admin' ? 'processAdminAction' : 'processAction' }}">
                    Memproses...
                </span>
            </button>
        </div>
    </div>
</div>
