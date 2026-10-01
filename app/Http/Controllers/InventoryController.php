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

        $products = Product::select('products.*', DB::raw('COALESCE(inventories.quantity, 0) as display_quantity'))
            ->leftJoin('inventories', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'inventories.product_id')
                     ->where('inventories.branch_id', '=', $branchId);
            })
            ->orderBy('products.id', 'asc')
            ->get();

        return view('inventory.index', compact('user', 'products'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:255',
            'size'        => 'nullable|string|max:255',
            'quantity'    => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $branchId = auth()->user()->branch_id ?? 1;

            $existing = Product::findDuplicate([
                'name'  => $validated['name'],
                'brand' => $validated['brand'],
                'type'  => $validated['type'],
                'size'  => $validated['size'] ?? null,
                'color' => $validated['color'] ?? null,
            ]);

            if ($existing) {
                $quantity = (int) $validated['quantity'];

                // firstOrCreate + increment matches the POS pattern and keeps the
                // arithmetic in SQL with real parameter binding.
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $existing->id, 'branch_id' => $branchId],
                    ['quantity' => 0]
                );
                $inventory->increment('quantity', $quantity);

                // Keep the denormalised product total in step with the branch
                // inventory, the way the normal create path and POS both do.
                $existing->increment('quantity', $quantity);

                DB::commit();

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('success', "Product already exists (Serial: {$existing->serial_number}) — no duplicate created. Stock was added to the existing product instead.");
            }

            $product = Product::create([
                'name'        => $validated['name'],
                'brand'       => $validated['brand'],
                'type'        => $validated['type'],
                'color'       => $validated['color'] ?? null,
                'size'        => $validated['size'] ?? null,
                'quantity'    => $validated['quantity'],
                'price'       => $validated['price'],
                'description' => $validated['description'] ?? null,
                'branch_id'   => $branchId,
            ]);

            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branchId],
                ['quantity' => $validated['quantity']]
            );

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