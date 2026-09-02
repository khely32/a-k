<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\StockTransfer;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Only the owner can access reports.');
        }

        $branches = Branch::orderBy('branch_name')->get();

        $branchId = $request->get('branch_id', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $data = $this->getReportData($branchId, $startDate, $endDate);

        $user = Auth::user();
        $userBranchId = (int) $user->branch_id;
        $userBranch = $userBranchId ? Branch::find($userBranchId) : null;
        $isMainBranch = $userBranch && $userBranch->isMainBranch();

        return view('reports.index', array_merge($data, compact(
            'branches',
            'branchId',
            'startDate',
            'endDate',
            'userBranchId',
            'isMainBranch'
        )));
    }

    public function reportData(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403);
        }

        $branchId = $request->get('branch_id', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $raw = $this->getReportData($branchId, $startDate, $endDate);

        $raw['topProducts'] = $raw['topProducts']->map(function ($item) {
            $product = $item->product ?? null;
            if (!$product && isset($item->product_id)) {
                $product = \App\Models\Product::find($item->product_id);
            }
            return [
                'product_name' => $product->name ?? 'N/A',
                'brand'        => $product->brand ?? '-',
                'quantity'     => (int) ($item->quantity ?? $item->total_qty ?? 0),
                'price'        => (float) ($product->price ?? 0),
            ];
        })->toArray();

        return response()->json($raw);
    }

    private function getReportData($branchId, $startDate = null, $endDate = null)
    {
        $isAll = ($branchId === 'all' || $branchId === null);

        if ($isAll) {
            // ═══ OVERALL / ALL BRANCHES ═══
            $totalProducts = Inventory::where('quantity', '>', 0)
                ->distinct('product_id')
                ->count('product_id');

            $totalInventory = Inventory::sum('quantity');

            $allInventory = Inventory::all();
            $lowStockProducts = 0;
            $inStock = 0;
            $lowStock = 0;
            $outOfStock = 0;
            foreach ($allInventory as $item) {
                if ($item->quantity <= 0) {
                    $outOfStock++;
                } elseif ($item->quantity <= 5) {
                    $lowStock++;
                    $lowStockProducts++;
                } else {
                    $inStock++;
                }
            }

            $pendingTransfers = StockTransfer::where('status', 'pending')->count();

            $salesQuery = Sale::query();
            if ($startDate) {
                $salesQuery->where('created_at', '>=', Carbon::parse($startDate, 'Asia/Manila')->startOfDay());
            }
            if ($endDate) {
                $salesQuery->where('created_at', '<=', Carbon::parse($endDate, 'Asia/Manila')->endOfDay());
            }
            $totalRevenue = $salesQuery->sum('total_amount');

            $inventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                ->selectRaw('SUM(inventories.quantity * products.price) as total')
                ->value('total') ?? 0;

            $topProducts = Inventory::select('product_id', DB::raw('SUM(quantity) as quantity'))
                ->where('quantity', '>', 0)
                ->groupBy('product_id')
                ->orderByDesc('quantity')
                ->take(10)
                ->get();

        } else {
            // ═══ SINGLE BRANCH ═══
            $invQuery = Inventory::where('inventories.branch_id', $branchId);

            $totalProducts = Product::where('products.branch_id', $branchId)->count();
            $totalInventory = (clone $invQuery)->sum('inventories.quantity');

            $allInventory = (clone $invQuery)->get();
            $lowStockProducts = 0;
            $inStock = 0;
            $lowStock = 0;
            $outOfStock = 0;
            foreach ($allInventory as $item) {
                if ($item->quantity <= 0) {
                    $outOfStock++;
                } elseif ($item->quantity <= 5) {
                    $lowStock++;
                    $lowStockProducts++;
                } else {
                    $inStock++;
                }
            }

            $pendingTransfers = StockTransfer::where('status', 'pending')
                ->where(function ($q) use ($branchId) {
                    $q->where('from_branch_id', $branchId)
                      ->orWhere('to_branch_id', $branchId);
                })->count();

            $salesQuery = Sale::where('branch_id', $branchId);
            if ($startDate) {
                $salesQuery->where('created_at', '>=', Carbon::parse($startDate, 'Asia/Manila')->startOfDay());
            }
            if ($endDate) {
                $salesQuery->where('created_at', '<=', Carbon::parse($endDate, 'Asia/Manila')->endOfDay());
            }
            $totalRevenue = $salesQuery->sum('total_amount');

            $inventoryValue = (clone $invQuery)
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->selectRaw('SUM(inventories.quantity * products.price) as total')
                ->value('total') ?? 0;

            $topProducts = (clone $invQuery)->with('product')
                ->orderByDesc('inventories.quantity')
                ->take(10)
                ->get();
        }

        return compact(
            'totalProducts',
            'totalInventory',
            'lowStockProducts',
            'pendingTransfers',
            'inventoryValue',
            'totalRevenue',
            'topProducts',
            'inStock',
            'lowStock',
            'outOfStock'
        );
    }

    public function sales(Request $request)
    {
        $user = Auth::user();

        $query = Sale::with([
            'branch',
            'user',
            'items.product'
        ]);

        if (!in_array($user->role, ['owner', 'admin'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date, 'Asia/Manila')->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date, 'Asia/Manila')->endOfDay());
        }

        $sales = $query->latest()->get();
        $branches = Branch::all();

        return view('reports.sales', compact('sales', 'branches'));
    }

    public function transactionHistory(Request $request)
    {
        $user = Auth::user();

        $query = Sale::with([
            'branch',
            'user',
            'items.product'
        ]);

        if ($user->role !== 'owner') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date, 'Asia/Manila')->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date, 'Asia/Manila')->endOfDay());
        }

        $sales = $query->latest()->get();
        $branches = Branch::all();

        return view('reports.transaction_history', compact('sales', 'branches'));
    }

    public function transactionData(Request $request)
    {
        $user = Auth::user();

        $query = Sale::with([
            'branch',
            'user',
            'items.product'
        ]);

        if ($user->role !== 'owner') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date, 'Asia/Manila')->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date, 'Asia/Manila')->endOfDay());
        }

        $sales = $query->latest()->get();

        $data = $sales->map(function ($sale) {
            return [
                'id'             => $sale->id,
                'created_at'     => $sale->created_at->format('M d, Y'),
                'created_time'   => $sale->created_at->format('h:i A'),
                'branch'         => $sale->branch->branch_name ?? 'N/A',
                'cashier'        => $sale->user->name ?? 'N/A',
                'items'          => $sale->items->map(function ($item) {
                    return [
                        'quantity' => (int) $item->quantity,
                        'name'     => $item->product->name ?? 'Unknown',
                    ];
                }),
                'payment_method' => $sale->payment_method ?? 'cash',
                'total'          => (float) $sale->total_amount,
            ];
        });

        return response()->json([
            'timestamp'   => now()->toIso8601String(),
            'sales'       => $data,
            'grand_total' => round($data->sum('total'), 2),
        ]);
    }

    public function inventory()
    {
        $user = Auth::user();

        $query = Inventory::with([
            'product',
            'branch'
        ]);

        if (!in_array($user->role, ['owner', 'admin'])) {
            $query->where('branch_id', $user->branch_id);
        }

        $inventories = $query->get();

        return view(
            'reports.inventory',
            compact('inventories')
        );
    }

    public function branchAnalytics(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403);
        }

        $branchId = $request->get('branch_id', 'all');
        $months = 6;
        $now = Carbon::now('Asia/Manila');

        $palette = ['#10B981', '#06B6D4', '#F59E0B', '#8B5CF6', '#F43F5E', '#6366F1'];

        $monthLabels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthLabels[] = $now->copy()->subMonths($i)->format('M Y');
        }

        $branches = Branch::orderBy('branch_name')->get();
        $branchMonthly = [];
        $branchTotals = [];

        foreach ($branches as $br) {
            $bid = (int) $br->id;
            $monthly = [];
            $totalRevenue = 0;

            for ($i = $months - 1; $i >= 0; $i--) {
                $mStart = $now->copy()->subMonths($i)->startOfMonth()->startOfDay();
                $mEnd = $now->copy()->subMonths($i)->endOfMonth()->endOfDay();

                $rev = Sale::where('branch_id', $bid)
                    ->whereBetween('created_at', [$mStart, $mEnd])
                    ->sum('total_amount');

                $monthly[] = round((float) $rev, 2);
                $totalRevenue += (float) $rev;
            }

            $branchMonthly[$bid] = $monthly;
            $branchTotals[$bid] = round($totalRevenue, 2);
        }

        $analytics = [];
        $branchIndex = 0;

        foreach ($branches as $br) {
            $bid = (int) $br->id;
            $monthly = $branchMonthly[$bid] ?? [];
            $revenue = $branchTotals[$bid] ?? 0;

            $maxMonth = max($monthly ?: [0]);
            $target = round($maxMonth * 1.25, 2);
            if ($target < 1000) $target = 1000;

            $avgMonthly = count($monthly) > 0 ? array_sum($monthly) / count($monthly) : 0;
            $completionRate = $target > 0 ? round(($avgMonthly / $target) * 100, 1) : 0;

            $prevMonth = count($monthly) >= 2 ? $monthly[count($monthly) - 2] : 0;
            $currMonth = count($monthly) >= 1 ? $monthly[count($monthly) - 1] : 0;
            $growthRate = $prevMonth > 0 ? round((($currMonth - $prevMonth) / $prevMonth) * 100, 1) : 0;

            $delta = $target - $avgMonthly;

            $analytics[$bid] = [
                'branch_id'       => $bid,
                'branch_name'     => $br->isMainBranch() ? 'Main Branch' : $br->branch_name,
                'color'           => $palette[$branchIndex % count($palette)],
                'monthly'         => $monthly,
                'total_revenue'   => round($revenue, 2),
                'target'          => $target,
                'avg_monthly'     => round($avgMonthly, 2),
                'completion_rate' => $completionRate,
                'delta'           => round($delta, 2),
                'growth_rate'     => $growthRate,
            ];

            $branchIndex++;
        }

        $comparison = [];
        foreach ($analytics as $a) {
            $comparison[] = [
                'branch'    => $a['branch_name'],
                'revenue'   => $a['total_revenue'],
                'target'    => $a['target'],
                'rate'      => $a['completion_rate'],
                'growth'    => $a['growth_rate'],
                'color'     => $a['color'],
            ];
        }

        return response()->json([
            'months'      => $monthLabels,
            'branches'    => $analytics,
            'comparison'  => $comparison,
        ]);
    }
}