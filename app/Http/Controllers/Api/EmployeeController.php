<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Employee Dashboard - Statistics and recent data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $statistics = [
            'my_customers' => $user->customers()->count(),
            'my_orders' => $user->orders()->count(),
            'today_orders' => $user->orders()->whereDate('created_at', today())->count(),
            'today_revenue' => $user->orders()
                                   ->whereDate('created_at', today())
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount'),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'processing_orders' => $user->orders()->where('status', 'processing')->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
        ];

        $recent_orders = $user->orders()
                              ->with(['customer', 'price'])
                              ->latest()
                              ->limit(5)
                              ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
                'recent_orders' => $recent_orders
            ]
        ]);
    }

    /**
     * Customer Management - List customers
     */
    public function customers(Request $request)
    {
        $user = $request->user();
        $query = $user->customers();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'total_pages' => $customers->lastPage(),
                'total_items' => $customers->total(),
                'per_page' => $customers->perPage(),
            ]
        ]);
    }

    /**
     * Create new customer
     */
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = $request->user()->customers()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer created successfully'
        ], 201);
    }

    /**
     * Show customer detail
     */
    public function showCustomer(Customer $customer)
    {
        // Check if customer belongs to authenticated user
        if ($customer->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer->load(['orders' => function($query) {
            $query->with('price')->latest()->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    /**
     * Update customer
     */
    public function updateCustomer(Request $request, Customer $customer)
    {
        // Check if customer belongs to authenticated user
        if ($customer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($request->only(['name', 'email', 'phone', 'address']));

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer updated successfully'
        ]);
    }

    /**
     * Delete customer
     */
    public function destroyCustomer(Customer $customer)
    {
        // Check if customer belongs to authenticated user
        if ($customer->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Check if customer has orders
        if ($customer->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete customer who has orders'
            ], 400);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Search customers for quick selection
     */
    public function searchCustomers(Request $request)
    {
        $user = $request->user();
        $search = $request->get('q', '');

        $customers = $user->customers()
                          ->where(function($query) use ($search) {
                              $query->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                          })
                          ->limit(10)
                          ->get(['id', 'name', 'email', 'phone']);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Order Management - List orders
     */
    public function orders(Request $request)
    {
        $user = $request->user();
        $query = $user->orders()->with(['customer', 'price']);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->latest()->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
                'total_items' => $orders->total(),
                'per_page' => $orders->perPage(),
            ]
        ]);
    }

    /**
     * Create new order
     */
    public function storeOrder(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'price_id' => 'required|exists:prices,id',
            'weight' => 'required|numeric|min:0.1',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'order_date' => 'nullable|date',
        ]);

        $user = $request->user();
        
        // Verify customer belongs to user
        $customer = $user->customers()->findOrFail($request->customer_id);
        
        // Verify price belongs to user
        $price = $user->prices()->where('is_active', true)->findOrFail($request->price_id);

        // Calculate total
        $weight = $request->weight;
        $unitPrice = $price->price;
        $discount = $request->discount ?? 0;
        $totalAmount = ($weight * $unitPrice) - $discount;

        // Generate invoice number
        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now())->count() + 1;
        $invoice = 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'invoice' => $invoice,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'price_id' => $price->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'order_date' => $request->order_date ?? now(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'weight' => $weight,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'payment_method' => $request->payment_method,
        ]);

        $order->load(['customer', 'price']);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order created successfully'
        ], 201);
    }

    /**
     * Show order detail
     */
    public function showOrder(Order $order)
    {
        // Check if order belongs to authenticated user
        if ($order->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->load(['customer', 'price']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        // Check if order belongs to authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'pickup_date' => 'nullable|date',
        ]);

        $data = ['status' => $request->status];

        if ($request->has('payment_status')) {
            $data['payment_status'] = $request->payment_status;
        }

        if ($request->has('pickup_date')) {
            $data['pickup_date'] = $request->pickup_date;
        }

        $order->update($data);
        $order->load(['customer', 'price']);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Helper Methods - Get active prices for this employee
     */
    public function getActivePrices(Request $request)
    {
        $user = $request->user();
        $prices = $user->prices()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }
}
