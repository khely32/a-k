<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    // SHOW POS PAGE
    public function index()
    {
        return view('pos.index');
    }

    // ⚡ SEARCH PRODUCT (optional category filter; empty search = available feed)
    public function search(Request $request)
    {
        $search = trim($request->search ?? '');
        $category = trim($request->category ?? '');
        $branchId = auth()->user()->branch_id;

        $query = Product::select([
                'products.id',
                'products.name as part_name',
                'products.serial_number as item_code',
                'products.price',
                'inventories.quantity as stock_level',
                'products.brand',
                'products.type'
            ])
            ->join('inventories', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'inventories.product_id')
                     ->where('inventories.branch_id', '=', $branchId)
                     ->where('inventories.quantity', '>', 0);
            });

        if ($category !== '' && strtolower($category) !== 'all') {
            $query->where('products.type', $category);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('products.serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('products.name', 'LIKE', "%{$search}%")
                  ->orWhere('products.brand', 'LIKE', "%{$search}%")
                  ->orWhere('products.type', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('products.name')->limit(120)->get();

        return response()->json($products);
    }

    // 🗂 CATEGORIES (for the filter dropdown)
    public function categories()
    {
        $categories = Product::whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->values();

        return response()->json($categories);
    }

    // ✅ ADD TO CART
    public function addToCart(Request $request)
    {
        $productId = $request->input('id');
        $product = Product::findOrFail($productId);
        $branchId = auth()->user()->branch_id;

        $inventory = Inventory::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        $availableQty = $inventory ? (int) $inventory->quantity : 0;

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['qty'] < $availableQty) {
                $cart[$product->id]['qty']++;
            }
        } else {
            if ($availableQty > 0) {
                $cart[$product->id] = [
                    "name"  => $product->name,
                    "sku"   => $product->serial_number,
                    "price" => $product->price,
                    "brand" => $product->brand,
                    "type"  => $product->type,
                    "stock" => $availableQty,
                    "qty"   => 1
                ];
            }
        }

        session()->put('cart', $cart);
        return response()->json($cart);
    }

    // ➕➖ UPDATE QUANTITY
    public function updateQty(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->input('id');
        $qty = (int) $request->input('qty');
        $branchId = auth()->user()->branch_id;

        if (isset($cart[$productId])) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();
            $max = $inventory ? (int) $inventory->quantity : 0;
            $cart[$productId]['qty'] = max(1, min($qty, $max));
            session()->put('cart', $cart);
        }

        return response()->json($cart);
    }

    // GET CART
    public function getCart()
    {
        return response()->json(session()->get('cart', []));
    }

    // REMOVE ITEM FROM CART
    public function removeFromCart(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->input('id');
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }
        session()->put('cart', $cart);
        return response()->json($cart);
    }

    // CLEAR CART
    public function clearCart()
    {
        session()->forget('cart');
        return response()->json([]);
    }

    // ✅ CHECKOUT & UPDATE INVENTORY
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty!'], 400);
        }

        try {
            DB::transaction(function () use ($cart, $request) {
                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += $item['price'] * $item['qty'];
                }

                $taxRate = 0.12;
                $tax = round($subtotal * $taxRate, 2);
                $grandTotal = round($subtotal + $tax, 2);

                // Create sale record
                $sale = Sale::create([
                    'total_amount'   => $grandTotal,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'customer_id'    => $request->customer_id ?? null,
                    'user_id'        => auth()->id(),
                    'branch_id'      => auth()->user()->branch_id,
                ]);

                $branchId = auth()->user()->branch_id;

                foreach ($cart as $productId => $item) {
                    $product = Product::findOrFail($productId);

                    $inventory = Inventory::firstOrCreate(
                        ['product_id' => $productId, 'branch_id' => $branchId],
                        ['quantity' => 0]
                    );

                    $inventoryQty = (int) $inventory->quantity;
                    if ($inventoryQty < $item['qty']) {
                        throw new \Exception("Insufficient stock for: " . $product->name);
                    }

                    // Decrement sales from the selling branch's inventory (source of truth)
                    $inventory->decrement('quantity', $item['qty']);
                    $product->decrement('quantity', $item['qty']);

                    // Save sale item
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $productId,
                        'quantity'   => $item['qty'],
                        'price'      => $item['price'],
                    ]);
                }
            });

            session()->forget('cart');
            return response()->json(['success' => '✅ Sale completed and stock updated!']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}