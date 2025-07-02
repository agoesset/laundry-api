<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Admin Dashboard Statistics
     */
    public function dashboard()
    {
        $statistics = [
            'total_employees' => User::where('role', 'employee')->count(),
            'active_employees' => User::where('role', 'employee')->where('is_active', true)->count(),
            'total_customers' => Customer::count(),
            'total_orders' => Order::count(),
            'total_prices' => Price::where('is_active', true)->count(),
            
            // Today statistics
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount'),
            
            // Order status breakdown
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            
            // Revenue statistics
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'month_revenue' => Order::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount'),
        ];

        $recent_orders = Order::with(['customer', 'user', 'price'])
                              ->latest()
                              ->limit(10)
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
     * Employee Management - List all employees
     */
    public function employees(Request $request)
    {
        $query = User::where('role', 'employee');

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('branch_name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $employees = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    /**
     * Create new employee
     */
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'is_active' => true,
            'branch_name' => $request->branch_name,
            'branch_address' => $request->branch_address,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee
        ], 201);
    }

    /**
     * Show employee details
     */
    public function showEmployee(User $employee)
    {
        if ($employee->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an employee'
            ], 404);
        }

        // Load relationships
        $employee->load(['customers', 'prices', 'orders']);
        
        // Add statistics
        $employee->statistics = [
            'total_customers' => $employee->customers()->count(),
            'total_orders' => $employee->orders()->count(),
            'total_revenue' => $employee->orders()
                                       ->where('payment_status', 'paid')
                                       ->sum('total_amount'),
            'pending_orders' => $employee->orders()
                                        ->where('status', 'pending')
                                        ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, User $employee)
    {
        if ($employee->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an employee'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($employee->id)],
            'password' => 'nullable|min:8|confirmed',
            'is_active' => 'boolean',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'name', 
            'email', 
            'is_active', 
            'branch_name', 
            'branch_address', 
            'address', 
            'phone'
        ]);
        
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee
        ]);
    }

    /**
     * Delete employee (soft delete recommended in production)
     */
    public function destroyEmployee(User $employee)
    {
        if ($employee->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an employee'
            ], 404);
        }

        // Check if employee has orders
        if ($employee->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete employee with existing orders. Consider deactivating instead.'
            ], 400);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    /**
     * Price Management - List all prices
     */
    public function prices(Request $request)
    {
        $query = Price::with('user');

        // Search
        if ($request->search) {
            $query->where('service_type', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting
        $query->orderBy($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc');

        $prices = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $prices->items(),
            'meta' => [
                'current_page' => $prices->currentPage(),
                'last_page' => $prices->lastPage(),
                'per_page' => $prices->perPage(),
                'total' => $prices->total(),
            ]
        ]);
    }

    /**
     * Create new price
     */
    public function storePrice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $price = Price::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Price created successfully',
            'data' => $price->load('user')
        ], 201);
    }

    /**
     * Show price details
     */
    public function showPrice(Price $price)
    {
        return response()->json([
            'success' => true,
            'data' => $price->load('user')
        ]);
    }

    /**
     * Update price
     */
    public function updatePrice(Request $request, Price $price)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $price->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully',
            'data' => $price->load('user')
        ]);
    }

    /**
     * Delete price
     */
    public function destroyPrice(Price $price)
    {
        // Check if price is used in orders
        if ($price->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete price that is used in orders. Consider deactivating instead.'
            ], 400);
        }

        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price deleted successfully'
        ]);
    }
}
