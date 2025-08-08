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
        // FormRequest sudah melakukan validasi secara otomatis.
        // Jika validasi gagal, Laravel akan mengirim response error 422.

        $user = $request->user();
        $validatedData = $request->validated();

        // $request->validated() hanya akan mengembalikan data yang sudah tervalidasi
        $user->update($request->validated());

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan foto baru dan dapatkan path-nya
            $path = $request->file('photo')->store('profile-photos', 'public');
            $validatedData['profile_photo_path'] = $path;
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user,
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
