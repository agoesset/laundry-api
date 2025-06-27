<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;
use App\Models\Transaction;
use App\Models\Price;
use App\Models\LaundrySetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transaction Controller untuk API
 * 
 * Menangani CRUD operations untuk transaksi laundry
 * Customer bisa melihat transaksi mereka sendiri
 * Admin/Karyawan bisa melihat semua transaksi
 */
class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Base query dengan relationships
        $query = Transaction::with(['customer', 'user', 'price']);
        
        // Filter berdasarkan role
        if ($user->isCustomer()) {
            // Customer hanya bisa lihat transaksi sendiri
            $query->where('customer_id', $user->id);
        }
        
        // Filter berdasarkan parameter
        if ($request->has('status_order')) {
            $query->where('status_order', $request->status_order);
        }
        
        if ($request->has('status_payment')) {
            $query->where('status_payment', $request->status_payment);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('tgl_transaksi', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('tgl_transaksi', '<=', $request->date_to);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $transactions = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data' => $transactions,
        ], 200);
    }

    /**
     * Store a newly created transaction
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(TransactionStoreRequest $request): JsonResponse
    {
        // Validasi otomatis handled oleh TransactionStoreRequest
        $validated = $request->validated();
        
        $user = $request->user();
        
        // Get price details
        $price = Price::findOrFail($validated['price_id']);
        if (!$price->isActive()) {
            throw ValidationException::withMessages([
                'price_id' => ['Harga layanan tidak aktif'],
            ]);
        }
        
        // Get laundry settings untuk validasi
        $settings = LaundrySetting::getActive();
        $totalHarga = $price->harga * $validated['kg'];
        if ($settings && $totalHarga < $settings->minimum_order) {
            throw ValidationException::withMessages([
                'kg' => ['Minimum order Rp ' . number_format($settings->minimum_order, 0, ',', '.')],
            ]);
        }
        
        // Validate discount
        $discount = $validated['discount'] ?? 0;
        if ($settings && $settings->allow_discount && $discount > 0) {
            $maxDiscountAmount = ($price->harga * $validated['kg']) * ($settings->max_discount_percent / 100);
            if ($discount > $maxDiscountAmount) {
                throw ValidationException::withMessages([
                    'discount' => ["Maksimal diskon adalah {$settings->max_discount_percent}%"],
                ]);
            }
        } elseif ($discount > 0 && (!$settings || !$settings->allow_discount)) {
            throw ValidationException::withMessages([
                'discount' => ['Diskon tidak diizinkan'],
            ]);
        }
        
        // Get customer details
        $customer = \App\Models\User::findOrFail($validated['customer_id']);
        
        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'price_id' => $price->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'kg' => $validated['kg'],
                'hari' => $price->hari,
                'harga' => $price->harga,
                'discount' => $discount,
                'total_harga' => ($price->harga * $validated['kg']) - $discount,
                'status_order' => $validated['status_order'] ?? 'Process',
                'status_payment' => $validated['status_payment'] ?? 'Pending',
            ]);
            
            // Auto-generate invoice sudah di handle di model
            
            // TODO: Send notification (WhatsApp/Email/Telegram)
            
            DB::commit();
            
            // Load relationships untuk response
            $transaction->load(['customer', 'user', 'price']);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction,
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified transaction
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Find transaction dengan relationships
        $transaction = Transaction::with(['customer', 'user', 'price'])->find($id);
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
        
        // Check permission
        if ($user->isCustomer() && $transaction->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke transaksi ini',
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil',
            'data' => $transaction,
        ], 200);
    }

    /**
     * Update the specified transaction
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(TransactionUpdateRequest $request, string $id): JsonResponse
    {
        // Validasi otomatis handled oleh TransactionUpdateRequest
        $validated = $request->validated();
        
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
        
        // Validasi business logic
        if (isset($validated['status_order'])) {
            // Tidak bisa mundur status
            $statusFlow = ['Process' => 1, 'Done' => 2, 'Delivery' => 3];
            $currentStatus = $statusFlow[$transaction->status_order];
            $newStatus = $statusFlow[$validated['status_order']];
            
            if ($newStatus < $currentStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mengubah status ke tahap sebelumnya',
                ], 422);
            }
            
            // Jika status Done/Delivery, payment harus Success
            $paymentStatus = $validated['status_payment'] ?? $transaction->status_payment;
            if (in_array($validated['status_order'], ['Done', 'Delivery']) && 
                $paymentStatus !== 'Success') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran harus lunas sebelum status Done/Delivery',
                ], 422);
            }
        }
        
        DB::beginTransaction();
        try {
            // Update transaction
            $transaction->update($validated);
            
            // Update customer points jika transaksi selesai dan dibayar
            if ($transaction->status_order === 'Done' && 
                $transaction->status_payment === 'Success' &&
                $transaction->wasChanged('status_order')) {
                
                $customer = $transaction->customer;
                $pointsEarned = floor($transaction->total_harga / 10000); // 1 point per 10rb
                $customer->increment('point', $pointsEarned);
            }
            
            // TODO: Send notification untuk status update
            
            DB::commit();
            
            // Reload dengan relationships
            $transaction->load(['customer', 'user', 'price']);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data' => $transaction,
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal update transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified transaction (soft delete)
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        // Hanya admin yang bisa hapus
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang bisa menghapus transaksi',
            ], 403);
        }
        
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
        
        // Tidak bisa hapus transaksi yang sudah selesai
        if ($transaction->status_order === 'Done' || $transaction->status_order === 'Delivery') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus transaksi yang sudah selesai',
            ], 422);
        }
        
        try {
            $transaction->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction summary/statistics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admin and karyawan can access summary
        if (!$user->isAdmin() && !$user->isKaryawan()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke summary transaksi',
            ], 403);
        }
        
        // Base query
        $query = Transaction::query();
        
        // Filter periode
        $period = $request->get('period', 'month'); // month, year, all
        if ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', now()->year);
        }
        
        // Calculate summary
        $summary = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->where('status_payment', 'Success')->sum('total_harga'),
            'pending_transactions' => (clone $query)->where('status_payment', 'Pending')->count(),
            'completed_transactions' => (clone $query)->where('status_order', 'Done')->count(),
            'total_kg' => $query->sum('kg'),
            'status_breakdown' => [
                'process' => (clone $query)->where('status_order', 'Process')->count(),
                'done' => (clone $query)->where('status_order', 'Done')->count(),
                'delivery' => (clone $query)->where('status_order', 'Delivery')->count(),
            ],
            'payment_breakdown' => [
                'pending' => (clone $query)->where('status_payment', 'Pending')->count(),
                'success' => (clone $query)->where('status_payment', 'Success')->count(),
            ],
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Summary transaksi berhasil diambil',
            'data' => $summary,
        ], 200);
    }
}
