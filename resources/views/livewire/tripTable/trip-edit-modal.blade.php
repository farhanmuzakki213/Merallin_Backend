<div x-show="$wire.showModal" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5" x-cloak>
    <div @click="$wire.closeModal()" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-sm"></div>

    {{-- Mengubah max-w-2xl menjadi max-w-3xl agar lebih lebar untuk layout 2 kolom --}}
    <div @click.outside="$wire.closeModal()"
        class="no-scrollbar relative w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 dark:bg-boxdark lg:p-8 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b pb-4 dark:border-gray-800">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $tripId ? 'Edit Trip' : 'Create New Trip' }}
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
            {{-- Menggunakan class yang sama dari template Anda untuk konsistensi --}}
            @php
                $inputStyle =
                    'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-boxdark-2 dark:focus:border-brand-500';
                $errorStyle = 'mt-1 text-xs text-red-500';
            @endphp

            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                {{-- Detail Proyek --}}
                <div class="sm:col-span-2">
                    <label for="projectName"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Project Name</label>
                    <input wire:model="projectName" id="projectName" type="text" class="{{ $inputStyle }}"
                        placeholder="Contoh: Pengiriman Semen Holcim">
                    @error('projectName')
                        <span class="{{ $errorStyle }}">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Bagian Origin --}}
                <div class="sm:col-span-2 mt-4 border-t pt-5 dark:border-gray-800">
                    <h5 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">📍 Origin Details</h5>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="origin_address" class="mb-1.5 block text-sm font-medium">Alamat Lengkap</label>
                            <input wire:model="origin_address" id="origin_address" type="text"
                                class="{{ $inputStyle }}" placeholder="Masukkan alamat lengkap">
                            @error('origin_address')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="origin_link" class="mb-1.5 block text-sm font-medium">Link Google Maps</label>
                            <input wire:model="origin_link" id="origin_link" type="url" class="{{ $inputStyle }}"
                                placeholder="https://maps.app.goo.gl/...">
                            @error('origin_link')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mt-4 border-t pt-5 dark:border-gray-800">
                    <h5 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">🏁 Destination Details</h5>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="destination_address" class="mb-1.5 block text-sm font-medium">Alamat
                                Lengkap</label>
                            <input wire:model="destination_address" id="destination_address" type="text"
                                class="{{ $inputStyle }}" placeholder="Masukkan alamat lengkap">
                            @error('destination_address')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="destination_link" class="mb-1.5 block text-sm font-medium">Link Google
                                Maps</label>
                            <input wire:model="destination_link" id="destination_link" type="url"
                                class="{{ $inputStyle }}" placeholder="https://maps.app.goo.gl/...">
                            @error('destination_link')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Bagian Jumlah Gudang --}}
                <div class="sm:col-span-2 mt-4 border-t pt-5 dark:border-gray-800">
                    <h5 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">📦 Gudang Details</h5>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="jumlah_gudang_muat" class="mb-1.5 block text-sm font-medium">Jumlah Gudang
                                Muat</label>
                            <input wire:model="jumlah_gudang_muat" id="jumlah_gudang_muat" type="number" min="1"
                                class="{{ $inputStyle }}">
                            @error('jumlah_gudang_muat')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="jumlah_gudang_bongkar" class="mb-1.5 block text-sm font-medium">Jumlah Gudang
                                Bongkar</label>
                            <input wire:model="jumlah_gudang_bongkar" id="jumlah_gudang_bongkar" type="number"
                                min="1" class="{{ $inputStyle }}">
                            @error('jumlah_gudang_bongkar')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Bagian Detail Trip --}}
                <div class="sm:col-span-2 mt-4 border-t pt-5 dark:border-gray-800">
                    <h5 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">📋 Trip Details</h5>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="slot_time" class="mb-1.5 block text-sm font-medium">Slot Time</label>
                            <input wire:model="slot_time" id="slot_time" type="time" class="{{ $inputStyle }}">
                            @error('slot_time')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_berat" class="mb-1.5 block text-sm font-medium">Jenis Muatan</label>
                            <select wire:model="jenis_berat" id="jenis_berat" class="{{ $inputStyle }}">
                                <option value="" disabled>-- Pilih Jenis --</option>
                                <option value="CDDL">CDDL (> 8 Ton)</option>
                                <option value="CDDS">CDDS (< 8 Ton)</option>
                                <option value="CDE">CDE (< 4 Ton)</option>
                            </select>
                            @error('jenis_berat')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="userId"
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assign
                                Driver</label>
                            <select wire:model="userId" id="userId" class="{{ $inputStyle }}">
                                <option value="">-- Tidak Di-assign --</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                            @error('userId')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="jenisTrip"
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis
                                Trip</label>
                            <select wire:model="jenisTrip" id="jenisTrip" class="{{ $inputStyle }}">
                                <option value="muatan perusahan">Muatan Perusahaan</option>
                                <option value="muatan driver">Muatan Driver</option>
                            </select>
                            @error('jenisTrip')
                                <span class="{{ $errorStyle }}">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 border-t pt-5 dark:border-gray-800">
                <button @click="$wire.closeModal()" type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <span wire:loading.remove wire:target="save">
                        {{ $tripId ? 'Update Trip' : 'Create Trip' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
