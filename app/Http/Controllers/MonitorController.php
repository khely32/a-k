<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use App\Models\Product;

class MonitorController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Only the owner can access the monitoring dashboard.');
        }

        $branches = Branch::with('inventories.product')->get();
        $totalProducts = Product::count();
        $categories = Product::query()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('monitor.index', compact('branches', 'totalProducts', 'categories'));
    }

    public function stockData()
    {
        if (Auth::user()->role !== 'owner') {
            abort(403);
        }

        $branches = Branch::with(['inventories.product'])->get();

        $data = $branches->map(function ($branch) {
            $inventories = $branch->inventories->map(function ($inv) {
                return [
                    'product_id'    => $inv->product_id,
                    'product_name'  => $inv->product->name ?? 'Unknown',
                    'serial_number' => $inv->product->serial_number ?? '',
                    'brand'         => $inv->product->brand ?? '',
                    'category'      => $inv->product->type ?? '',
                    'quantity'      => (int) $inv->quantity,
                    'price'         => $inv->product->price ?? 0,
                    'status'        => $inv->quantity <= 0 ? 'out_of_stock' : ($inv->quantity <= 5 ? 'low_stock' : 'in_stock'),
                ];
            })->sortBy('product_name')->values();

            return [
                'branch_id'      => $branch->id,
                'branch_name'    => $branch->name,
                'branch_address' => $branch->address,
                'inventories'    => $inventories,
                'total_items'    => $inventories->count(),
                'low_stock_count' => $inventories->where('status', 'low_stock')->count(),
                'out_of_stock_count' => $inventories->where('status', 'out_of_stock')->count(),
            ];
        });

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'branches'  => $data,
        ]);
    }
}
