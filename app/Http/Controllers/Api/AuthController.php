<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Services\AwsRekognitionService;

class AuthController extends Controller
{
    public function __construct(protected AwsRekognitionService $awsRekognitionService)
    {
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'role' => $user->getRoleNames(),
            'meta' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request)
    {
        try {
            if (!$request->user() || !$request->user()->currentAccessToken()) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout berhasil',
                'meta' => [
                    'status' => 'success',
                    'code' => 200,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal logout'
            ], 401);
        }
    }

    public function register(Request $request)
    {
        // 1. TAMBAHAN PADA VALIDASI
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'alamat' => ['required', 'string', 'min:10', 'max:500'],
            'no_telepon' => ['required', 'string', 'min:10'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. TAMBAHAN SAAT MEMBUAT USER
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('karyawan');

        event(new Registered($user));

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user
        ], 201);
    }

    public function registerFace(Request $request)
    {
        $request->validate(['photo' => 'required|image']);
        $user = $request->user();

        $faceId = $this->awsRekognitionService->indexFace($request->file('photo'), $user->id);

        if (!$faceId) {
            return response()->json(['message' => 'Gagal mendaftarkan wajah di AWS.'], 500);
        }

        $user->aws_face_id = $faceId;
        $user->save();

        return response()->json(['message' => 'Wajah berhasil didaftarkan.']);
    }
}
