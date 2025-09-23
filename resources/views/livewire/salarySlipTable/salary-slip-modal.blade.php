<div x-show="$wire.showModal" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5" x-cloak>
    <div @click="$wire.closeModal()" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-sm"></div>

    <div @click.outside="$wire.closeModal()" class="no-scrollbar relative w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 dark:bg-boxdark lg:p-8">
        {{-- Header Modal Dinamis --}}
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-800">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $slipId ? 'Edit Salary Slip' : 'Upload New Salary Slip' }}
            </h4>
            <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"><path d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"></path></svg>
            </button>
        </div>

        <form wire:submit.prevent="save" class="pt-6">
            <div class="space-y-4">
                {{-- User Selection --}}
                <div>
                    <label for="userId" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Select User</label>
                    <select wire:model="userId" id="userId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                        <option value="">-- Choose a user --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('userId') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Period --}}
                <div>
                    <label for="period" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Salary Period (Month & Year)</label>
                    <input wire:model="period" id="period" type="month" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('period') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- File Upload --}}
                <div>
                    <label for="salarySlipFile" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Slip Gaji File (PDF)</label>
                    {{-- Perubahan: Tampilkan file yang ada saat mode edit --}}
                    @if ($slipId && $existingFilePath)
                        <div class="mb-2 text-sm text-gray-500">
                            Current file: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $existingFilePath }}</span>
                        </div>
                        <p class="text-xs text-gray-400">Leave empty if you don't want to change the file.</p>
                    @endif
                    <input wire:model="salarySlipFile" id="salarySlipFile" type="file" class="w-full rounded-lg border border-gray-300 bg-transparent text-sm file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:bg-gray-100 file:font-semibold hover:file:bg-gray-200 dark:border-gray-700 dark:bg-boxdark-2 dark:file:bg-gray-700 dark:file:text-gray-300">
                    @error('salarySlipFile') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Tombol Aksi Dinamis --}}
            <div class="flex items-center justify-end gap-3 mt-6 border-t pt-5 dark:border-gray-800">
                <button @click="$wire.closeModal()" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</button>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <span wire:loading.remove wire:target="save">
                        {{ $slipId ? 'Update Changes' : 'Upload & Save' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
