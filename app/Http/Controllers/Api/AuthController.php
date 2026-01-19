<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * REGISTER
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30|unique:users,username',
            'fullname' => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.required'  => 'Username harus diisi',
            'username.unique'    => 'Username sudah digunakan',
            'fullname.required'  => 'Nama lengkap harus diisi',
            'email.required'     => 'Email harus diisi',
            'password.required'  => 'Password harus diisi',
            'password.min'       => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'fullname' => $validated['fullname'],
            'email'    => $validated['email'],
            'password' => $validated['password'], 
            
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user'  => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(Request $request): JsonResponse
{
    $validated = $request->validate([
        'login' => 'required|string', 
        'password' => 'required|string',
    ]);

    $login = $validated['login'];

    $user = User::where('email', $login)
        ->orWhere('username', $login)
        ->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Username/email atau password salah'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => [
            'user' => $user,
            'token' => $token,
        ]
    ]);
}

    /**
     * LOGOUT
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * ME (user login + watchlist + movie)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu belum login',
            ], 401);
        }

        $user->load([
            'watchlists.movie'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data user login',
            'data' => $user,
        ]);
    }
}