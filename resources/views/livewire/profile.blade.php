<div
    x-data="{ isEditModalOpen: false, isPasswordModalOpen: false }"
    @profile-saved.window="isEditModalOpen = false"
    @password-updated.window="isPasswordModalOpen = false"
>
    <div x-data="{ pageName: 'Profile' }">
        @include('partials.breadcrumb')
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">

        {{-- Pesan sukses untuk update profil --}}
        @if (session('message'))
            <div class="mb-5 flex items-center gap-3 rounded-lg border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15">
                <div class="-mt-0.5 text-success-500">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm-1.954 13.268l-3.314-3.314a.5.5 0 01.707-.707l2.96 2.96 5.879-5.879a.5.5 0 11.707.707l-6.236 6.236a.5.5 0 01-.353.146z"></path></svg>
                </div>
                <div>
                    <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Success</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        {{-- Pesan sukses untuk update password --}}
        @if (session('password-message'))
            <div class="mb-5 flex items-center gap-3 rounded-lg border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15">
                <div class="-mt-0.5 text-success-500">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm-1.954 13.268l-3.314-3.314a.5.5 0 01.707-.707l2.96 2.96 5.879-5.879a.5.5 0 11.707.707l-6.236 6.236a.5.5 0 01-.353.146z"></path></svg>
                </div>
                <div>
                    <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Success</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ session('password-message') }}</p>
                </div>
            </div>
        @endif

        <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-col items-center w-full gap-6 xl:flex-row">
                    <div class="relative h-20 w-20 rounded-full">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="New Profile" class="h-20 w-20 rounded-full object-cover">
                        @elseif (Auth::user()->profile_photo_path)
                            <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="user" class="h-20 w-20 rounded-full object-cover">
                        @else
                            <img src="{{ asset('images/user/owner.jpg') }}" alt="user" class="h-20 w-20 rounded-full object-cover">
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90 xl:text-left">
                            {{ $name }}
                        </h4>
                        <p class="text-sm text-center text-gray-500 dark:text-gray-400 xl:text-left">
                            {{ Auth::user()->alamat ?? 'Alamat belum diisi' }}
                        </p>
                    </div>
                </div>
                {{-- Grup Tombol --}}
                <div class="flex flex-col items-center gap-3 lg:flex-row">
                    <button @click="isPasswordModalOpen = true" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] lg:inline-flex lg:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                        Change Password
                    </button>
                    <button @click="isEditModalOpen = true" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] lg:inline-flex lg:w-auto">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18"><path d="M15.091 2.782a2.33 2.33 0 00-3.298 0L4.575 10.116a2.33 2.33 0 00-.642 1.127l-.652 3.093a.7.7 0 00.844.844l3.093-.652a2.33 2.33 0 001.127-.642l7.218-7.218a2.33 2.33 0 000-3.298l-.663-.663zm-2.121 1.06l.663.663a.83.83 0 010 1.172L6.956 13.015l-1.936.408.408-1.936L13.015 3.842a.83.83 0 011.172 0z"></path></svg>
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>

        <div class="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
                Personal Information
            </h4>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                <div>
                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Full Name</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $name }}</p>
                </div>
                <div>
                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email Address</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $email }}</p>
                </div>
                <div>
                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Phone Number</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $no_telepon ?? 'Not Set' }}</p>
                </div>
                <div>
                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Address</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $alamat ?? 'Not Set' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modal --}}
    @include('partials.edit-profile-modal')
    @livewire('update-password-form')
</div>
