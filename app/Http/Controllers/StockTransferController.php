<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Only the Owner sees every transfer. Branch staff see only
        // transfers where their branch is involved (from OR to).
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'product']);

        if (strtolower($user->role) !== 'owner') {
            $query->where(function ($q) use ($user) {
                $q->where('from_branch_id', $user->branch_id)
                  ->orWhere('to_branch_id', $user->branch_id);
            });
        }

        $transfers = $query->latest()->get();
        $userBranch = $user->branch;

        return view('transfers.index', compact('transfers', 'userBranch'));
    }

    public function create()
    {
        $user = Auth::user();
        $products = Product::orderBy('name', 'asc')->get();

        $branches = Branch::orderBy('branch_name')->get();
        $sourceBranch = null;
        $destinationBranch = null;

        if (strtolower($user->role) === 'owner') {
            $sourceBranch = null;
            $destinationBranch = null;
        } else {
            if (!$user->branch_id) {
                abort(403, 'Your account has no branch assigned. Contact the owner.');
            }
            $destinationBranch = Branch::findOrFail($user->branch_id);
            $sourceBranch = null;
        }

        return view('transfers.create', compact('products', 'branches', 'sourceBranch', 'destinationBranch'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (strtolower($user->role) === 'owner') {
            $request->validate([
                'from_branch_id' => 'required|exists:branches,id',
                'to_branch_id'   => 'required|exists:branches,id',
                'product_id'     => 'required|exists:products,id',
                'quantity'       => 'required|integer|min:1',
            ]);

            if ($request->from_branch_id === $request->to_branch_id) {
                return back()->withErrors(['to_branch_id' => 'Source and destination branches must be different.'])->withInput();
            }

            $fromBranch = $request->from_branch_id;
            $toBranch   = $request->to_branch_id;
        } else {
            $request->validate([
                'from_branch_id' => 'required|exists:branches,id',
                'product_id'     => 'required|exists:products,id',
                'quantity'       => 'required|integer|min:1',
            ]);

            $allowed = Branch::where('id', '!=', $user->branch_id)
                ->pluck('id');
            if (!$allowed->contains($request->from_branch_id)) {
                return back()->withErrors(['from_branch_id' => 'Invalid source branch.'])->withInput();
            }
            $fromBranch = $request->from_branch_id;
            $toBranch   = $user->branch_id;
        }

        StockTransfer::create([
            'from_branch_id' => $fromBranch,
            'to_branch_id'   => $toBranch,
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'status'         => 'pending',
        ]);

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Stock transfer request submitted successfully.');
    }

    public function checkStock(Request $request)
    {
        $request->validate([
            'source_branch_id' => 'required|exists:branches,id',
            'product_id'       => 'required|exists:products,id',
        ]);

        $sourceBranchId = (int) $request->source_branch_id;
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'quantity' => 0,
                'status'   => 'out_of_stock',
                'label'    => 'Out of Stock',
            ]);
        }

        $inventory = Inventory::where('branch_id', $sourceBranchId)
            ->where('product_id', $product->id)
            ->first();
        $quantity = $inventory ? (int) $inventory->quantity : 0;

        return response()->json([
            'quantity' => $quantity,
            'status'   => $quantity <= 0 ? 'out_of_stock' : ($quantity <= 5 ? 'low_stock' : 'in_stock'),
            'label'    => $quantity <= 0 ? 'Out of Stock' : ($quantity <= 5 ? 'Low Stock (' . $quantity . ')' : 'In Stock (' . $quantity . ')'),
        ]);
    }

    public function history()
    {
        $user = Auth::user();

        // Mirror the same role-based visibility as the transfers list.
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'product']);

        if (strtolower($user->role) !== 'owner') {
            $query->where(function ($q) use ($user) {
                $q->where('from_branch_id', $user->branch_id)
                  ->orWhere('to_branch_id', $user->branch_id);
            });
        }

        $transfers = $query->latest()->get();
        return view('transfers.history', compact('transfers'));
    }

    public function approve($id)
    {
        $stockTransfer = StockTransfer::findOrFail($id);

        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', 'This transfer has already been processed.');
        }

        $productId    = $stockTransfer->product_id;
        $fromBranchId = $stockTransfer->from_branch_id;
        $toBranchId   = $stockTransfer->to_branch_id;
        $transferQty  = $stockTransfer->quantity;

        $product = Product::findOrFail($productId);

        $sourceInventory = Inventory::firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $fromBranchId],
            ['quantity' => 0]
        );

        if ($sourceInventory->quantity < $transferQty) {
            return back()->with('error', "Transfer failed. Source branch only has {$sourceInventory->quantity} units available.");
        }

        DB::transaction(function () use ($stockTransfer, $sourceInventory, $product, $productId, $fromBranchId, $toBranchId, $transferQty) {
            $sourceInventory->decrement('quantity', $transferQty);
            $product->decrement('quantity', $transferQty);

            $destInventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'branch_id' => $toBranchId],
                ['quantity' => 0]
            );
            $destInventory->increment('quantity', $transferQty);

            $stockTransfer->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Stock transfer completed successfully.');
    }

    public function reject($id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'This transfer has already been processed.');
        }

        $transfer->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('transfers.index')->with('success', 'Transfer rejected successfully.');
    }

    public function confirmReceipt($id)
    {
        if (strtolower(Auth::user()->role) !== 'owner') {
            abort(403, 'Only the owner can manage stock transfers.');
        }

        $transfer = StockTransfer::findOrFail($id);
        $transfer->status = 'received';
        $transfer->save();
        return redirect()->back()->with('success', 'Transfer received successfully.');
    }
}
