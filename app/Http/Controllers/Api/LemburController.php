<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLemburRequest;
use App\Http\Resources\LemburResource;
use App\Models\Lembur;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LemburController extends Controller
{
    /**
     * Menampilkan daftar data lembur milik user yang sedang login.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $lembur = $user->lemburs()->latest()->get();

            if ($lembur->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data lembur yang ditemukan.',
                    'data' => [],
                ], 200);
            }

            return LemburResource::collection($lembur)->response();

        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching lembur data: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data lembur.'], 500);
        }
    }

    /**
     * Menyimpan data lembur baru.
     */
    public function store(StoreLemburRequest $request): JsonResponse
    {
        try {
            // Validasi sudah otomatis ditangani oleh StoreLemburRequest

            // Mengambil data yang sudah tervalidasi
            $validatedData = $request->validated();

            // Menambahkan user_id dari user yang sedang login
            $validatedData['user_id'] = Auth::id();

            // Membuat data lembur baru
            $lembur = Lembur::create($validatedData);

            // Mengembalikan respons sukses dengan data yang baru dibuat
            return response()->json([
                'message' => 'Data lembur berhasil ditambahkan.',
                'data' => new LemburResource($lembur)
            ], 201); // 201 Created

        } catch (\Illuminate\Validation\ValidationException $e) {
            // This is already handled by the form request, but as a fallback
            return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error storing lembur data: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data lembur.'], 500);
        }
    }
}
