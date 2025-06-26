<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceStoreRequest;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Price Controller untuk API
 * 
 * Menangani CRUD operations untuk harga layanan laundry
 * Hanya Admin yang bisa create, update, delete
 * Semua user bisa melihat harga
 */
class PriceController extends Controller
{
    /**
     * Display a listing of prices
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Base query dengan user yang membuat
        $query = Price::with('user:id,name,email');
        
        // Filter status - default hanya yang aktif untuk customer
        if ($request->user()->isCustomer() || !$request->has('show_all')) {
            $query->active();
        }
        
        // Filter berdasarkan jenis
        if ($request->has('jenis')) {
            $query->byJenis($request->jenis);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'jenis');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'price') {
            $query->orderByPrice($sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        // Get all prices (no pagination untuk price list)
        $prices = $query->get();
        
        // Group by jenis jika diminta
        if ($request->get('group_by_jenis', false)) {
            $prices = $prices->groupBy('jenis');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Data harga berhasil diambil',
            'data' => $prices,
        ], 200);
    }

    /**
     * Store a newly created price
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(PriceStoreRequest $request): JsonResponse
    {
        // Validasi otomatis handled oleh PriceStoreRequest
        $validated = $request->validated();
        
        DB::beginTransaction();
        try {
            // Create price
            $price = Price::create([
                'user_id' => $request->user()->id,
                'jenis' => $validated['jenis'],
                'kg' => $validated['kg'],
                'harga' => $validated['harga'],
                'hari' => $validated['hari'],
                'status' => $validated['status'],
            ]);
            
            DB::commit();
            
            // Load user relationship
            $price->load('user:id,name,email');
            
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil ditambahkan',
                'data' => $price,
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah harga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified price
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $price = Price::with('user:id,name,email')->find($id);
        
        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Harga tidak ditemukan',
            ], 404);
        }
        
        // Tambah info estimasi selesai dari hari ini
        $price->estimasi_selesai = $price->getEstimasiSelesai();
        
        return response()->json([
            'success' => true,
            'message' => 'Detail harga berhasil diambil',
            'data' => $price,
        ], 200);
    }

    /**
     * Update the specified price
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Check permission - hanya admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang bisa mengubah harga',
            ], 403);
        }
        
        $price = Price::find($id);
        
        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Harga tidak ditemukan',
            ], 404);
        }
        
        // Validasi input
        $validated = $request->validate([
            'jenis' => 'sometimes|string|max:255',
            'kg' => 'sometimes|string|max:50',
            'harga' => 'sometimes|numeric|min:0|max:1000000',
            'hari' => 'sometimes|integer|min:1|max:30',
            'status' => 'sometimes|in:Active,Inactive',
        ]);
        
        // Check duplicate jenis jika diubah
        if (isset($validated['jenis']) && $validated['jenis'] !== $price->jenis) {
            $exists = Price::where('jenis', $validated['jenis'])
                          ->where('status', 'Active')
                          ->where('id', '!=', $id)
                          ->exists();
                          
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis layanan dengan nama tersebut sudah ada',
                ], 422);
            }
        }
        
        // Warning jika ada transaksi yang menggunakan harga ini
        if ($price->transactions()->exists()) {
            $transactionCount = $price->transactions()->count();
            
            // Jika ingin menonaktifkan, beri warning
            if (isset($validated['status']) && $validated['status'] === 'Inactive') {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak bisa menonaktifkan harga yang sudah digunakan di {$transactionCount} transaksi",
                ], 422);
            }
        }
        
        DB::beginTransaction();
        try {
            // Update price
            $price->update($validated);
            
            DB::commit();
            
            // Reload dengan user
            $price->load('user:id,name,email');
            
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil diupdate',
                'data' => $price,
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal update harga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified price
     * 
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        // Check permission - hanya admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang bisa menghapus harga',
            ], 403);
        }
        
        $price = Price::find($id);
        
        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Harga tidak ditemukan',
            ], 404);
        }
        
        // Tidak bisa hapus jika sudah digunakan di transaksi
        if ($price->transactions()->exists()) {
            $transactionCount = $price->transactions()->count();
            
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa menghapus harga yang sudah digunakan di {$transactionCount} transaksi. Nonaktifkan saja.",
            ], 422);
        }
        
        try {
            $price->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil dihapus',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus harga',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of unique jenis layanan
     * 
     * @return JsonResponse
     */
    public function getJenisList(): JsonResponse
    {
        $jenisList = Price::active()
                         ->distinct()
                         ->pluck('jenis')
                         ->sort()
                         ->values();
        
        return response()->json([
            'success' => true,
            'message' => 'List jenis layanan berhasil diambil',
            'data' => $jenisList,
        ], 200);
    }
}
