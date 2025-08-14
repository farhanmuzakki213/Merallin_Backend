<div x-show="$wire.showModal" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5" x-cloak>
    <div @click="$wire.closeModal()" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-sm"></div>

    <div @click.outside="$wire.closeModal()"
        class="no-scrollbar relative w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 dark:bg-boxdark lg:p-8">
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-800">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                Edit User: {{ $name }}
            </h4>
            <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24">
                    <path
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z">
                    </path>
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="save" class="pt-6">
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">

                {{-- Name --}}
                <div class="sm:col-span-2">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Full
                        Name</label>
                    <input wire:model="name" id="name" type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('name')
                        <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Email (disabled) --}}
                <div class="sm:col-span-2">
                    <label for="email"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Address</label>
                    <input wire:model="email" id="email" type="email" disabled
                        class="h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm dark:border-gray-700 dark:bg-boxdark-2/50 dark:text-gray-400">
                </div>

                {{-- Alamat --}}
                <div class="sm:col-span-2">
                    <label for="alamat"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Address</label>
                    <textarea wire:model="alamat" id="alamat" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500"></textarea>
                    @error('alamat')
                        <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- No Telepon --}}
                <div>
                    <label for="no_telepon"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone Number</label>
                    <input wire:model="no_telepon" id="no_telepon" type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('no_telepon')
                        <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Roles</label>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        @foreach ($allRoles as $role)
                            <label for="role-{{ $role }}" class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" wire:model="userRoles" id="role-{{ $role }}"
                                    value="{{ $role }}"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($role) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('userRoles')
                        <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Leave password fields blank to keep the current
                        password.</p>
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">New
                        Password</label>
                    <input wire:model="password" id="password" type="password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                    @error('password')
                        <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm New
                        Password</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500">
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button @click="$wire.closeModal()" type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
