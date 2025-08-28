<div x-data="{ dropdownOpen: false }" wire:poll.15s="fetchNotifications">
    <button @click="dropdownOpen = !dropdownOpen; if(dropdownOpen) { $wire.markAsRead() }"
        class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">

        @if($unreadCount > 0)
        <span class="absolute top-0.5 right-0 z-1 h-2 w-2 rounded-full bg-orange-400">
            <span class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
        </span>
        @endif

        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z" fill=""></path>
            </svg>
        </button>

        <div x-show="dropdownOpen" @click.outside="dropdownOpen = false"
            class="shadow-theme-lg dark:bg-gray-dark absolute -right-[240px] mt-[17px] flex h-auto max-h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 sm:w-[361px] lg:right-0 dark:border-gray-800"
            x-cloak>
            <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Notifikasi
                </h5>
                {{-- Opsi untuk tandai semua sudah dibaca tanpa membuka dropdown lagi --}}
                @if($unreadCount > 0)
                    <button wire:click="markAsRead()" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                        Tandai Semua Dibaca
                    </button>
                @endif
            </div>

            <ul class="custom-scrollbar flex flex-col overflow-y-auto">
                @forelse ($notifications as $notification)
                    <li>
                        <a class="flex items-start gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5 @if(!$notification->read_at) font-semibold bg-blue-50/50 dark:bg-gray-700/30 @endif"
                           href="{{ $notification->data['url'] ?? '#' }}">
                            {{-- Icon Notifikasi (Contoh) --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @if(isset($notification->data['type']) && $notification->data['type'] == 'trip_photo')
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                @else
                                    {{-- Default icon --}}
                                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h2l2-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v10l2 2h2m2 0h2a2 2 0 002-2v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2h2zm-7 0H7a2 2 0 00-2-2v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2h2z" />
                                    </svg>
                                @endif
                            </div>

                            <span class="block flex-grow">
                                <span class="text-theme-sm mb-0.5 block @if(!$notification->read_at) text-gray-800 dark:text-white/90 @else text-gray-600 dark:text-white/70 @endif">
                                    {{-- Judul Notifikasi Lebih Detail --}}
                                    @if(isset($notification->data['title']))
                                        <strong>{{ $notification->data['title'] }}</strong>
                                    @else
                                        <strong>Pembaruan Sistem</strong>
                                    @endif
                                </span>
                                <span class="text-theme-sm mb-1.5 block @if(!$notification->read_at) text-gray-700 dark:text-gray-200 @else text-gray-500 dark:text-gray-400 @endif">
                                    {{ $notification->data['message'] }}
                                </span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                </span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi baru.</li>
                @endforelse
            </ul>
        </div>
    </div>
