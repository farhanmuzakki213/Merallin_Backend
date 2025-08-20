<div x-data="{ show: @entangle('showPdfPreviewModal').live }" x-show="show" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99998] flex items-center justify-center bg-black bg-opacity-75 p-4" x-cloak>

    <div @click.outside="$wire.closePdfPreview()" class="w-full max-w-4xl rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pratinjau Surat Perintah Kerja Lembur</h3>
            <div class="flex items-center gap-2">
                <button onclick="printPreview()" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Cetak</button>
                <button @click="$wire.closePdfPreview()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
        </div>

        <div class="max-h-[80vh] overflow-y-auto p-2 sm:p-6">
            @if ($lemburForPdf)
                <div id="print-area" class="bg-white p-4 font-serif text-sm text-black">
                    {{-- KOP SURAT --}}
                    <div class="border-b-2 border-black pb-2 text-center">
                        <h1 class="text-xl font-bold">PT. MERALLIN SUKSES ABADI</h1>
                        <p class="text-xs">Head Office: JL. Gading Kirana Timur A11, Kelapa Gading, Jakarta Utara 14240</p>
                        <p class="text-xs">Branch Office: Komple Permata Cimahi Jl. Permata Raya Blok D1 No 1 40552</p>
                    </div>

                    {{-- DETAIL --}}
                    <div class="mt-6 grid grid-cols-2 gap-x-8 gap-y-2">
                        <div><span class="font-bold">Hari/Tanggal:</span> {{ \Carbon\Carbon::parse($lemburForPdf->tanggal_lembur)->isoFormat('dddd, D MMMM Y') }}</div>
                        <div><span class="font-bold">Department:</span> {{ $lemburForPdf->department }}</div>
                        <div><span class="font-bold">Jenis Hari:</span> {{ $lemburForPdf->jenis_hari }}</div>
                    </div>
                    <div class="mt-2"><span class="font-bold">Keterangan Lembur:</span> {{ $lemburForPdf->keterangan_lembur }}</div>

                    {{-- TABEL KARYAWAN --}}
                    <div class="mt-6">
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
                                    <td class="border border-black p-1 text-center">{{ $lemburForPdf->user->nik ?? 'N/A' }}</td>
                                    <td class="border border-black p-1">{{ $lemburForPdf->user->name ?? 'N/A' }}</td>
                                    <td class="border border-black p-1 text-center">{{ \Carbon\Carbon::parse($lemburForPdf->mulai_jam_lembur)->format('H:i') }}</td>
                                    <td class="border border-black p-1 text-center">{{ \Carbon\Carbon::parse($lemburForPdf->selesai_jam_lembur)->format('H:i') }}</td>
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
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<script>
    function printPreview() {
        window.print();
    }
</script>
