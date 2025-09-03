<div>
    {{-- Breadcrumb dan Session Message --}}
    <div x-data="{ pageName: 'Salary Slips Management' }">@include('partials.breadcrumb')</div>
    @if (session('message'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Kontrol & Tombol --}}
            <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Kontrol Entri --}}
                <div class="flex items-center gap-3">
                    <select wire:model.live="perPage" class="dark:bg-dark-900 h-9 w-auto appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pr-8 pl-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
                {{-- Pencarian & Tombol Create --}}
                <div class="flex items-center gap-3">
                    <input type="text" placeholder="Search by user or file name..." wire:model.live.debounce.300ms="search" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <button wire:click="openModal()" class="flex h-11 items-center justify-center whitespace-nowrap rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                        Upload Slip
                    </button>
                </div>
            </div>

            {{-- Tabel Data --}}
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">File Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Uploaded At</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @forelse ($slips as $slip)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $slip->user->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $slip->period->format('F Y') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $slip->original_file_name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $slip->created_at->format('d M Y, H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <a href="{{ $slip->file }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">View</a>
                                    <button wire:click="edit({{ $slip->id }})" class="ml-4 text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-200">Edit</button>
                                    <button wire:click="delete({{ $slip->id }})" wire:confirm="Are you sure you want to delete this slip?" class="ml-4 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No salary slips found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginasi --}}
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $slips->links() }}
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        @include('livewire.salarySlipTable.salary-slip-modal')
    @endif
</div>
