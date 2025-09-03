<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalarySlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SharedFileController extends Controller
{
    /**
     * Menyajikan file slip gaji secara aman berdasarkan UUID-nya.
     */
    public function serveSalarySlip(Request $request, string $uuid): StreamedResponse
    {
        // 1. Cari slip gaji berdasarkan UUID. Gagal jika tidak ditemukan.
        $slip = SalarySlip::where('uuid', $uuid)->firstOrFail();

        // 2. Otorisasi: Pastikan pengguna yang terautentikasi adalah pemilik slip gaji.
        if ($request->user()->id !== $slip->user_id) {
            abort(403, 'Unauthorized access.');
        }

        // 3. Verifikasi keberadaan file di storage.
        if (!Storage::disk('public')->exists($slip->file_path)) {
            abort(404, 'File not found.');
        }

        // 4. Sajikan file untuk diunduh dengan nama file aslinya.
        return Storage::disk('public')->download($slip->file_path);
    }
}
