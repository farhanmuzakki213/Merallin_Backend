<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIzinRequest;
use App\Models\Izin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    /**
     * Menampilkan riwayat izin untuk pengguna yang sedang login.
     */
    public function index(): JsonResponse
    {
        $izins = Izin::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'message' => 'Riwayat izin berhasil diambil.',
            'data' => $izins
        ], 200);
    }

    /**
     * Membuat pengajuan izin baru.
     */
    public function store(StoreIzinRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('bukti')) {
            $path = $request->file('bukti')->store('public/bukti_izin');
            $validated['url_bukti'] = $path;
        }

        $validated['user_id'] = Auth::id();

        $izin = Izin::create($validated);

        return response()->json([
            'message' => 'Pengajuan izin berhasil dibuat.',
            'data' => $izin
        ], 201);
    }
}
