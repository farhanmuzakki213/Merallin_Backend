<div x-show="$wire.showModal" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5" x-cloak>
    <div @click="$wire.closeModal()" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-sm"></div>

    <div @click.outside="$wire.closeModal()"
        class="no-scrollbar relative w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 dark:bg-boxdark lg:p-8">
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-800">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $vehicleId ? 'Edit Vehicle' : 'Create New Vehicle' }}
            </h4>
            <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                {{-- SVG Close Icon --}}
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"><path d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"></path></svg>
            </button>
        </div>

        <form wire:submit.prevent="save" class="pt-6">
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">

                {{-- License Plate --}}
                <div class="sm:col-span-2">
                    <label for="license_plate" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">License Plate</label>
                    <input wire:model="license_plate" id="license_plate" type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('license_plate') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Model --}}
                <div>
                    <label for="model" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Vehicle Model</label>
                    <input wire:model="model" id="model" type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('model') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Vehicle Type</label>
                    <input wire:model="type" id="type" type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('type') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button @click="$wire.closeModal()" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
