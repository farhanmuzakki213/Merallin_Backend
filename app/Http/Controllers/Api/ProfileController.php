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
    $user = $request->user();
    $validatedData = $request->validated();

    if ($request->hasFile('photo')) {
        if ($user->profile_photo_path) {
            $oldPath = str_replace(Storage::url(''), '', $user->profile_photo_path);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');

        $validatedData['profile_photo_path'] = Storage::url($path);
    }

    $user->update($validatedData);

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
