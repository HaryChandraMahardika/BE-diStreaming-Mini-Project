<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_user'=> 'required|string|max:255',
            'email'=> 'required|email|unique:users,email',
            'password'=> 'required|string|min:8|confirmed',
        ], 
        [
            'name_user.required' => 'Nama user harus diisi',
            'email.required' => 'Email harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password harus sesuai',
        ]);

        $user = User::create([
            'name_user'=> $validated['name_user'],
            'email' => $validated['email'],
            'password'=> Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'=> true,
            'message'=> 'Registrasi berhasil',
            'data'=> [
                'user'=> $user,
                'token'=> $token,
            ],
        ], status:201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'=> 'required|email',
            'password'=> 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password))
        {
            return response()->json([
            'success'=> false,
            'message'=> 'Email atau password salah',
        ], status: 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'=> true,
            'message'=> 'Login berhasil',
            'data'=> [
                'user'=> $user,
                'token'=> $token,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
       $request->user()->currentAccessToken()->delete();

       return response()->json([
            'success'=> true,
            'message'=> 'Logout berhasil',
         ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        if($request->user() === null){
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak terautenikasi'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data User',
            'data' => $request->user()
        ]);
    }
}