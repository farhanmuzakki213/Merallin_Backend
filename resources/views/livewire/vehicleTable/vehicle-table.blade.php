<div>
    <div x-data="{ pageName: 'Vehicles Data Table' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('message'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Kontrol Entri & Tombol Create --}}
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400"> Show </span>
                    <select wire:model.live="perPage"
                        class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                    <span class="text-gray-500 dark:text-gray-400"> entries </span>
                </div>
                <div class="flex items-center gap-3">
                    <input type="text" placeholder="Search..." wire:model.live.debounce.300ms="search"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @hasanyrole(['admin', 'manager'])
                        <button wire:click="openModal()"
                            class="flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                            Create Vehicle
                        </button>
                    @endhasanyrole
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <div class="min-w-[900px]">
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                        <div class="col-span-4 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800"
                            wire:click="sortBy('license_plate')">
                            <p class="cursor-pointer text-theme-xs font-medium text-gray-700 dark:text-gray-400">License
                                Plate</p>
                        </div>
                        <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800"
                            wire:click="sortBy('model')">
                            <p class="cursor-pointer text-theme-xs font-medium text-gray-700 dark:text-gray-400">Model
                            </p>
                        </div>
                        <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800"
                            wire:click="sortBy('type')">
                            <p class="cursor-pointer text-theme-xs font-medium text-gray-700 dark:text-gray-400">Type
                            </p>
                        </div>
                        <div class="col-span-2 flex items-center px-4 py-3">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Action</p>
                        </div>
                    </div>
                    {{-- Table Body --}}
                    @forelse ($vehicles as $vehicle)
                        <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-4 flex items-center border-r border-gray-100 p-3 dark:border-gray-800">
                                <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $vehicle->license_plate }}</p>
                            </div>
                            <div class="col-span-3 flex items-center border-r border-gray-100 p-3 dark:border-gray-800">
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">{{ $vehicle->model ?? '-' }}
                                </p>
                            </div>
                            <div class="col-span-3 flex items-center border-r border-gray-100 p-3 dark:border-gray-800">
                                <p class="text-theme-sm text-gray-700 dark:text-gray-400">{{ $vehicle->type ?? '-' }}
                                </p>
                            </div>
                            <div class="col-span-2 flex items-center px-4 py-3">
                                @hasanyrole(['admin', 'manager'])
                                    <div class="flex w-full items-center gap-2">
                                        <button wire:click="edit({{ $vehicle->id }})"
                                            class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90">
                                            {{-- SVG Edit Icon --}}
                                            <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M17.0911 3.53206C16.2124 2.65338 14.7878 2.65338 13.9091 3.53206L5.6074 11.8337C5.29899 12.1421 5.08687 12.5335 4.99684 12.9603L4.26177 16.445C4.20943 16.6931 4.286 16.9508 4.46529 17.1301C4.64458 17.3094 4.90232 17.3859 5.15042 17.3336L8.63507 16.5985C9.06184 16.5085 9.45324 16.2964 9.76165 15.988L18.0633 7.68631C18.942 6.80763 18.942 5.38301 18.0633 4.50433L17.0911 3.53206ZM14.9697 4.59272C15.2626 4.29982 15.7375 4.29982 16.0304 4.59272L17.0027 5.56499C17.2956 5.85788 17.2956 6.33276 17.0027 6.62565L16.1043 7.52402L14.0714 5.49109L14.9697 4.59272ZM13.0107 6.55175L6.66806 12.8944C6.56526 12.9972 6.49455 13.1277 6.46454 13.2699L5.96704 15.6283L8.32547 15.1308C8.46772 15.1008 8.59819 15.0301 8.70099 14.9273L15.0436 8.58468L13.0107 6.55175Z"
                                                    fill=""></path>
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $vehicle->id }})"
                                            wire:confirm="Are you sure you want to delete this vehicle?"
                                            class="hover:text-error-500 dark:hover:text-error-500 text-gray-500 dark:text-gray-400">
                                            {{-- SVG Delete Icon --}}
                                            <svg class="fill-current" width="21" height="21" viewBox="0 0 21 21"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M7.04142 4.29199C7.04142 3.04935 8.04878 2.04199 9.29142 2.04199H11.7081C12.9507 2.04199 13.9581 3.04935 13.9581 4.29199V4.54199H16.1252H17.166C17.5802 4.54199 17.916 4.87778 17.916 5.29199C17.916 5.70621 17.5802 6.04199 17.166 6.04199H16.8752V16.7087C16.8752 17.9513 15.8678 18.9587 14.6252 18.9587H6.37516C5.13252 18.9587 4.12516 17.9513 4.12516 16.7087V6.04199H3.8335C3.41928 6.04199 3.0835 5.70621 3.0835 5.29199C3.0835 4.87778 3.41928 4.54199 3.8335 4.54199H4.87516H7.04142V4.29199ZM8.54142 4.54199H12.4581V4.29199C12.4581 3.87778 12.1223 3.54199 11.7081 3.54199H9.29142C8.87721 3.54199 8.54142 3.87778 8.54142 4.29199V4.54199Z"
                                                    fill=""></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endhasanyrole
                            </div>
                        </div>
                    @empty
                        <div class="grid grid-cols-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="col-span-1 p-4 text-center text-gray-500 dark:text-gray-400">
                                No vehicles found.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
                {{ $vehicles->links() }}
            </div>
        </div>
    </div>

    @if ($showModal)
        @include('livewire.vehicleTable.vehicle-edit-modal')
    @endif
</div>
