<div x-data="{ show: @entangle('showPdfPreviewModal').live }" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99998] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>

    <div @click.outside="$wire.closePdfPreview()"
        class="w-full max-w-4xl rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pratinjau Surat Perintah Kerja Lembur</h3>
            <div class="flex items-center gap-2">
                <button onclick="printPreview()" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Cetak</button>
                <button @click="$wire.closePdfPreview()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
        </div>

        {{-- [PERUBAHAN 1]: Area scroll diberi background abu-abu dan padding untuk jarak --}}
        <div class="max-h-[80vh] overflow-y-auto bg-gray-200 p-4 dark:bg-gray-900">
            @if ($lemburForPdf)
                {{-- [PERUBAHAN 2]: Area cetak diberi lebar seperti kertas A4 dan dibuat center --}}
                <div id="print-area"
                    class="relative mx-auto flex min-h-[1123px] w-[794px] flex-col bg-white font-serif text-sm text-black shadow-lg">

                    <!-- WATERMARK -->
                    <div class="absolute inset-0 z-0 flex items-center justify-center pointer-events-none">
                        <img src="{{ asset('images/logo/logo-header.svg') }}" alt="Watermark" class="w-2/3 opacity-5">
                    </div>

                    {{-- BANNER HEADER --}}
                    <div class="w-[820px] -mx-2">
                        <img src="{{ asset('images/logo/header.svg') }}" alt="Header Logo" class="w-full">
                        {{-- Gambar ini sekarang akan menempel di tepi kiri dan kanan "kertas" --}}
                    </div>

                    {{-- KONTEN SURAT --}}
                    {{-- [PERUBAHAN 3]: Konten diberi padding agar tidak menempel di tepi --}}
                    <div class="flex-grow p-8">
                        <div class="flex items-start justify-between gap-x-8 border-b-2 border-black pb-4">

                            {{-- SISI KIRI: Logo --}}
                            <div class="ml-35">
                                <img src="{{ asset('images/logo/logo-header.svg') }}" alt="Logo Header" class="w-auto">
                            </div>

                            {{-- SISI KANAN: Informasi Perusahaan --}}
                            <div class="-ml-5">
                                <h1 class="text-xl font-bold">PT. MERALLIN SUKSES ABADI</h1>
                                {{-- [PERUBAHAN]: Gunakan grid untuk meratakan colon --}}
                                <div class="grid grid-cols-[max-content_auto_1fr] gap-x-2 text-xs mt-3">

                                    {{-- Baris 1 --}}
                                    <span class="mt-2">Head Office</span>
                                    <span class="mt-2">:</span>
                                    <span class="mt-2">JL. Gading Kirana Timur A11, Kelapa Gading, Jakarta Utara 14240</span>

                                    {{-- Baris 2 --}}
                                    <span class="mt-2">Branch Office</span>
                                    <span class="mt-2">:</span>
                                    <span class="mt-2">Komple Permata Cimahi Jl. Permata Raya Blok D1 No 1 40552</span>

                                    {{-- Baris 3 --}}
                                    <span class="mt-2">Telpon</span>
                                    <span class="mt-2">:</span>
                                    <span class="mt-2">0858-6093-9031</span>
                                </div>

                            </div>

                        </div>


                        {{-- DETAIL --}}
                        <div class="mx-10 mt-6 grid grid-cols-[max-content_auto_1fr] gap-x-2 gap-y-2">
                            {{-- Baris 1 --}}
                            <span class="font-bold">Hari/Tanggal</span>
                            <span>:</span>
                            <div>{{ \Carbon\Carbon::parse($lemburForPdf->tanggal_lembur)->isoFormat('dddd, D MMMM Y') }}
                            </div>

                            {{-- Baris 2 --}}
                            <span class="font-bold">Department</span>
                            <span>:</span>
                            <div>{{ $lemburForPdf->department }}</div>

                            {{-- Baris 3 --}}
                            <span class="font-bold">Jenis Hari</span>
                            <span>:</span>
                            <div>{{ $lemburForPdf->jenis_hari }}</div>

                            {{-- Baris 4 --}}
                            <span class="font-bold">Keterangan Lembur</span>
                            <span>:</span>
                            <div>{{ $lemburForPdf->keterangan_lembur }}</div>
                        </div>

                        {{-- TABEL KARYAWAN --}}
                        <div class="mx-10 mt-6">
                            <table class="w-full border-collapse border border-black">
                                <thead>
                                    <tr class="bg-gray-200 text-center font-bold">
                                        <td class="border border-black p-1">No.</td>
                                        <td class="border border-black p-1">NIK KARYAWAN</td>
                                        <td class="border border-black p-1">NAMA KARYAWAN</td>
                                        <td class="border border-black p-1">MULAI LEMBUR</td>
                                        <td class="border border-black p-1">SELESAI LEMBUR</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-black p-1 text-center">1</td>
                                        <td class="border border-black p-1 text-center">
                                            {{ $lemburForPdf->user->nik ?? 'N/A' }}</td>
                                        <td class="border border-black p-1">{{ $lemburForPdf->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="border border-black p-1 text-center">
                                            {{ \Carbon\Carbon::parse($lemburForPdf->mulai_jam_lembur)->format('H:i') }}
                                        </td>
                                        <td class="border border-black p-1 text-center">
                                            {{ \Carbon\Carbon::parse($lemburForPdf->selesai_jam_lembur)->format('H:i') }}
                                        </td>
                                    </tr>
                                    @for ($i = 0; $i < 4; $i++)
                                        <tr>
                                            <td class="border border-black p-1 h-6"></td>
                                            <td class="border border-black p-1"></td>
                                            <td class="border border-black p-1"></td>
                                            <td class="border border-black p-1"></td>
                                            <td class="border border-black p-1"></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        {{-- TANDA TANGAN --}}
                        <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p>Dibuat oleh,</p>
                                <div class="pt-16">( {{ $lemburForPdf->user->name ?? '..................' }} )</div>
                                <p>Karyawan</p>
                            </div>
                            <div>
                                <p>Disetujui,</p>
                                <div class="pt-16">( ................................. )</div>
                                <p>Manager Operasional</p>
                            </div>
                            <div>
                                <p>Diketahui,</p>
                                <div class="pt-16">( ................................. )</div>
                                <p>Admin HRD</p>
                            </div>
                        </div>
                    </div>

                    {{-- BANNER FOOTER --}}
                    <div class="w-[794px] -mt-35">
                        {{-- Gambar ini juga akan menempel di tepi kiri dan kanan "kertas" --}}
                        <img src="{{ asset('images/logo/footer.svg') }}" alt="Footer Logo" class="w-full">
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
        }

        body * {
            visibility: hidden;
        }

        #print-area,
        #print-area * {
            visibility: visible;
        }

        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            box-shadow: none;
            /* Menghilangkan bayangan saat print */
        }
    }
</style>

<script>
    function printPreview() {
    // Simpan judul asli halaman
    const originalTitle = document.title;

    // Buat nama file yang Anda inginkan.
    // Anda bisa menggunakan data dari Livewire di sini.
    const fileName = "SPKL - {{ $lemburForPdf->user->name ?? 'User' }} - {{ $lemburForPdf->tanggal_lembur ?? 'Tanggal' }}";

    // Set judul halaman menjadi nama file yang baru
    document.title = fileName;

    // Panggil fungsi print
    window.print();

    // Kembalikan judul ke asli setelah dialog print muncul
    // Diberi sedikit jeda agar browser sempat memproses judul baru
    setTimeout(() => {
        document.title = originalTitle;
    }, 500);
}
</script>
