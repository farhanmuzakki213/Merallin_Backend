<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        // 1. TAMBAHAN PADA VALIDASI
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'alamat' => ['nullable', 'string', 'max:500'],        // <-- TAMBAHAN
            'no_telepon' => ['nullable', 'string', 'max:20'],     // <-- TAMBAHAN
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. TAMBAHAN SAAT MEMBUAT USER
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,                        // <-- TAMBAHAN
            'no_telepon' => $request->no_telepon,                // <-- TAMBAHAN
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
