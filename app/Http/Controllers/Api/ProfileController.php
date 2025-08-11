<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        // 1. Dapatkan pengguna dan data yang sudah divalidasi
    $user = $request->user();
    $validatedData = $request->validated();

    // 2. Cek jika ada file foto baru yang diunggah
    if ($request->hasFile('photo')) {
        // Hapus foto lama jika ada
        if ($user->profile_photo_path) {
            // Konversi URL lama kembali ke path relatif untuk dihapus
            $oldPath = str_replace(Storage::url(''), '', $user->profile_photo_path);
            Storage::disk('public')->delete($oldPath);
        }

        // Simpan foto baru di 'storage/app/public/profile-photos'
        $path = $request->file('photo')->store('profile-photos', 'public');

        // Buat URL lengkap dan tambahkan ke data yang akan diupdate
        $validatedData['profile_photo_path'] = Storage::url($path);
    }

    // 3. Update data pengguna dengan semua data baru (termasuk URL foto jika ada)
    $user->update($validatedData);

    // 4. Kembalikan response dengan data pengguna yang sudah ter-update
    return response()->json([
        'message' => 'Profil berhasil diperbarui.',
        'user' => $user->fresh(),
    ], 200);
    }

    /**
     * Mengupdate password pengguna yang terautentikasi.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('password'))
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ], 200);
    }
}
