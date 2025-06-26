<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Controller untuk API
 * 
 * Menangani proses login, register, logout, dan profile
 * menggunakan Laravel Sanctum untuk token authentication
 */
class AuthController extends Controller
{
    /**
     * Login user dan generate token
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'device_name' => 'required|string', // Nama device untuk token
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Validasi credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Validasi status aktif
        if ($user->status !== 'Active') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Silahkan hubungi admin.'],
            ]);
        }

        // Hapus token lama jika ada
        $user->tokens()->where('name', $request->device_name)->delete();

        // Generate token baru
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Response sukses dengan data user dan token
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->auth,
                    'point' => $user->point,
                    'foto_url' => $user->foto_url,
                    'no_telp' => $user->no_telp,
                    'alamat' => $user->alamat,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Register customer baru
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // password_confirmation
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'device_name' => 'required|string',
        ]);

        // Buat user baru dengan role Customer
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
            'auth' => 'Customer', // Default role untuk registrasi
            'status' => 'Active',
            'theme' => 'light',
            'point' => 0, // Point awal customer
        ]);

        // Generate token untuk auto-login
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Response sukses
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->auth,
                    'point' => $user->point,
                    'no_telp' => $user->no_telp,
                    'alamat' => $user->alamat,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Logout user dan hapus token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Logout dari semua device (hapus semua token)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Hapus semua token user
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout dari semua device berhasil',
        ], 200);
    }

    /**
     * Get authenticated user profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load relationships yang diperlukan
        if ($user->isAdmin() || $user->isKaryawan()) {
            $user->load(['bankAccounts' => function ($query) {
                $query->active();
            }]);
        }

        // Response dengan data user lengkap
        return response()->json([
            'success' => true,
            'message' => 'Data profile berhasil diambil',
            'data' => [
                'id' => $user->id,
                'karyawan_id' => $user->karyawan_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->auth,
                'status' => $user->status,
                'no_telp' => $user->no_telp,
                'alamat' => $user->alamat,
                'point' => $user->point,
                'theme' => $user->theme,
                'foto_url' => $user->foto_url,
                'nama_cabang' => $user->nama_cabang,
                'alamat_cabang' => $user->alamat_cabang,
                'bank_accounts' => $user->bankAccounts ?? [],
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ],
        ], 200);
    }

    /**
     * Update password authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updatePassword(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = $request->user();

        // Validasi password lama
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Logout dari semua device untuk keamanan
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silahkan login kembali.',
        ], 200);
    }

    /**
     * Check email availability untuk registrasi
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
                'message' => $exists ? 'Email sudah terdaftar' : 'Email tersedia',
            ],
        ], 200);
    }
}
