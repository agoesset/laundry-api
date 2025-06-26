<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * User Controller untuk API
 * 
 * Menangani profile management, customer list untuk admin
 * dan user-related operations
 */
class UserController extends Controller
{
    /**
     * Update user profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Validasi input
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'no_telp' => 'sometimes|string|max:20',
            'alamat' => 'sometimes|string',
            'theme' => 'sometimes|in:light,dark',
            'nama_cabang' => 'sometimes|string|max:255',
            'alamat_cabang' => 'sometimes|string',
        ]);
        
        // Update profile
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate',
            'data' => $user,
        ], 200);
    }

    /**
     * Upload/Update foto profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        // Validasi file
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);
        
        $user = $request->user();
        
        try {
            // Hapus foto lama jika ada
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            
            // Upload foto baru
            $path = $request->file('foto')->store('profile-photos', 'public');
            
            // Update database
            $user->update(['foto' => $path]);
            
            return response()->json([
                'success' => true,
                'message' => 'Foto profile berhasil diupdate',
                'data' => [
                    'foto_path' => $path,
                    'foto_url' => $user->foto_url,
                ],
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload foto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete foto profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->foto) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada foto untuk dihapus',
            ], 404);
        }
        
        try {
            // Hapus file
            if (Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            
            // Update database
            $user->update(['foto' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Foto profile berhasil dihapus',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus foto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of customers (for admin/karyawan)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomers(Request $request): JsonResponse
    {
        // Check permission
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isKaryawan()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat data customer',
            ], 403);
        }
        
        // Base query
        $query = User::byRole('Customer');
        
        // Filter status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // With transaction count
        $query->withCount(['customerTransactions as total_transactions']);
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $customers = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil diambil',
            'data' => $customers,
        ], 200);
    }

    /**
     * Get customer detail with transactions (for admin/karyawan)
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerDetail(string $id, Request $request): JsonResponse
    {
        // Check permission
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isKaryawan()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat detail customer',
            ], 403);
        }
        
        $customer = User::byRole('Customer')
                       ->withCount([
                           'customerTransactions as total_transactions',
                           'customerTransactions as pending_transactions' => function ($query) {
                               $query->where('status_order', 'Process');
                           },
                           'customerTransactions as completed_transactions' => function ($query) {
                               $query->where('status_order', 'Done');
                           },
                       ])
                       ->find($id);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan',
            ], 404);
        }
        
        // Load recent transactions
        $customer->load(['customerTransactions' => function ($query) {
            $query->with(['price', 'user'])
                  ->orderBy('created_at', 'desc')
                  ->limit(5);
        }]);
        
        // Calculate total spent
        $customer->total_spent = $customer->customerTransactions()
                                         ->where('status_payment', 'Success')
                                         ->sum('total_harga');
        
        return response()->json([
            'success' => true,
            'message' => 'Detail customer berhasil diambil',
            'data' => $customer,
        ], 200);
    }

    /**
     * Create new customer (for admin/karyawan)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomer(Request $request): JsonResponse
    {
        // Check permission
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isKaryawan()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membuat customer',
            ], 403);
        }
        
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'no_telp' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);
        
        try {
            // Create customer
            $customer = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'no_telp' => $validated['no_telp'],
                'alamat' => $validated['alamat'],
                'auth' => 'Customer',
                'status' => 'Active',
                'theme' => 'light',
                'point' => 0,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil dibuat',
                'data' => $customer,
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update customer status (for admin)
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCustomerStatus(string $id, Request $request): JsonResponse
    {
        // Check permission - hanya admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang bisa mengubah status customer',
            ], 403);
        }
        
        $customer = User::byRole('Customer')->find($id);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan',
            ], 404);
        }
        
        // Validasi input
        $validated = $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);
        
        // Update status
        $customer->update(['status' => $validated['status']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status customer berhasil diupdate',
            'data' => $customer,
        ], 200);
    }

    /**
     * Get user points history (for customer)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPointsHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Hanya customer yang punya points
        if (!$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur points hanya untuk customer',
            ], 403);
        }
        
        // Get transactions yang sudah selesai dan dapat points
        $transactions = $user->customerTransactions()
                            ->where('status_order', 'Done')
                            ->where('status_payment', 'Success')
                            ->select('id', 'invoice', 'total_harga', 'created_at')
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(function ($transaction) {
                                return [
                                    'invoice' => $transaction->invoice,
                                    'date' => $transaction->created_at->format('d/m/Y'),
                                    'amount' => $transaction->total_harga,
                                    'points_earned' => floor($transaction->total_harga / 10000),
                                ];
                            });
        
        return response()->json([
            'success' => true,
            'message' => 'History points berhasil diambil',
            'data' => [
                'current_points' => $user->point,
                'history' => $transactions,
            ],
        ], 200);
    }
}
