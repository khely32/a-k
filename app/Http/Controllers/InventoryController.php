<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (strtolower($user->role) === 'owner') {
            $products = Product::select('products.*', 'products.quantity as display_quantity')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $products = Product::select('products.*', DB::raw('COALESCE(inventories.quantity, 0) as display_quantity'))
                ->leftJoin('inventories', function ($join) use ($branchId) {
                    $join->on('products.id', '=', 'inventories.product_id')
                         ->where('inventories.branch_id', '=', $branchId);
                })
                ->orderBy('products.name', 'asc')
                ->get();
        }

        return view('inventory.index', compact('user', 'products'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:255|unique:products,serial_number',
            'name'          => 'required|string|max:255',
            'brand'         => 'nullable|string|max:255',
            'type'          => 'nullable|string|max:255',
            'quantity'      => 'required|integer|min:0',
            'price'         => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $branchId = auth()->user()->branch_id ?? 1;

            $product = Product::create([
                'serial_number' => $validated['serial_number'],
                'name'          => $validated['name'],
                'brand'         => $validated['brand'],
                'type'          => $validated['type'],
                'quantity'      => $validated['quantity'],
                'price'         => $validated['price'],
            ]);

            Inventory::create([
                'branch_id'  => $branchId,
                'product_id' => $product->id,
                'quantity'   => $validated['quantity'],
            ]);

            DB::commit();

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Product added successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}