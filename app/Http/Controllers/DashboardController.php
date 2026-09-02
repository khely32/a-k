<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockTransfer;
use App\Models\Branch;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (in_array($user->role, ['owner', 'admin'], true)) {
            $totalSales = Sale::where('branch_id', $branchId)->sum('total_amount');
            $totalProducts = Inventory::where('branch_id', $branchId)->distinct('product_id')->count('product_id');
            $totalInventory = Inventory::where('branch_id', $branchId)->sum('quantity');
            $lowStock = Inventory::where('branch_id', $branchId)
                ->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
            $outOfStock = Inventory::where('branch_id', $branchId)->where('quantity', 0)->count();
            $pendingTransfers = StockTransfer::where('status', 'pending')
                ->where(function ($q) use ($branchId) {
                    $q->where('from_branch_id', $branchId)
                      ->orWhere('to_branch_id', $branchId);
                })->count();

            $inventoryValue = Inventory::where('inventories.branch_id', $branchId)
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->selectRaw('SUM(inventories.quantity * products.price) as total')
                ->value('total') ?? 0;

            $topProducts = Inventory::where('inventories.branch_id', $branchId)
                ->with('product')
                ->orderByDesc('inventories.quantity')
                ->take(5)
                ->get();

            $recentSales = Sale::where('branch_id', $branchId)
                ->with(['user', 'items.product'])
                ->latest()
                ->take(5)
                ->get();

            $branchSummary = Branch::orderBy('id')->get()->map(function ($branch) {
                $branch->product_count = Inventory::where('branch_id', $branch->id)->count();
                $branch->total_qty = Inventory::where('branch_id', $branch->id)->sum('quantity');
                return $branch;
            });

            $stats = [
                'total_sales'     => $totalSales,
                'active_products' => $totalProducts,
                'low_stock'       => $lowStock,
                'out_of_stock'    => $outOfStock,
                'total_transfers' => $pendingTransfers,
                'total_products'  => $totalProducts,
                'total_inventory' => $totalInventory,
                'inventory_value' => $inventoryValue,
            ];

            return view('dashboard', compact('user', 'stats', 'topProducts', 'recentSales', 'branchSummary'));
        }

        // Staff
        $totalSales = Sale::where('branch_id', $branchId)->sum('total_amount');
        $totalProducts = Inventory::where('branch_id', $branchId)->distinct('product_id')->count('product_id');
        $totalInventory = Inventory::where('branch_id', $branchId)->sum('quantity');
        $lowStock = Inventory::where('branch_id', $branchId)
            ->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
        $outOfStock = Inventory::where('branch_id', $branchId)->where('quantity', 0)->count();
        $pendingTransfers = StockTransfer::where('status', 'pending')
            ->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            })->count();

        $inventoryValue = Inventory::where('inventories.branch_id', $branchId)
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity * products.price) as total')
            ->value('total') ?? 0;

        $topProducts = Inventory::where('inventories.branch_id', $branchId)
            ->with('product')
            ->orderByDesc('inventories.quantity')
            ->take(5)
            ->get();

        $recentSales = Sale::where('branch_id', $branchId)
            ->with(['user', 'items.product'])
            ->latest()
            ->take(5)
            ->get();

        $branchSummary = Branch::where('id', $branchId)->get()->map(function ($branch) {
            $branch->product_count = Inventory::where('branch_id', $branch->id)->count();
            $branch->total_qty = Inventory::where('branch_id', $branch->id)->sum('quantity');
            return $branch;
        });

        $stats = [
            'total_sales'     => $totalSales,
            'active_products' => $totalProducts,
            'low_stock'       => $lowStock,
            'out_of_stock'    => $outOfStock,
            'total_transfers' => $pendingTransfers,
            'total_products'  => $totalProducts,
            'total_inventory' => $totalInventory,
            'inventory_value' => $inventoryValue,
        ];

        return view('dashboard', compact('user', 'stats', 'topProducts', 'recentSales', 'branchSummary'));
    }
}
