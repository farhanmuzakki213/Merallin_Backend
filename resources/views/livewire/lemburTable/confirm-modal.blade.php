<div x-data="{ show: @entangle('showConfirmModal').live }" x-show="show" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>

    <div @click.outside="$wire.cancelConfirmation()" class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Tindakan</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" x-text="$wire.confirmMessage"></p>
        <div class="mt-6 flex justify-end space-x-3">
            <button @click="$wire.cancelConfirmation()" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Batal
            </button>
            <button @click="$wire.processAction()" type="button"
                :class="{
                    'bg-emerald-600 hover:bg-emerald-700': $wire.confirmAction === 'approve',
                    'bg-red-600 hover:bg-red-700': $wire.confirmAction === 'reject'
                }"
                class="inline-flex justify-center rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>
