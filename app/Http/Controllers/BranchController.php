<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\User;

class BranchController extends Controller
{
    /**
     * Display a listing of the branches.
     */
    public function index()
    {
        $branches = Branch::orderBy('branch_name', 'asc')->get()->map(function ($branch) {
            $branch->products_count = Product::where('branch_id', $branch->id)->count();
            $branch->users_count = User::where('branch_id', $branch->id)->count();
            $branch->revenue = number_format(Sale::where('branch_id', $branch->id)->sum('total_amount'), 2);
            $branch->low_stock = Inventory::where('branch_id', $branch->id)->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
            $branch->out_of_stock = Inventory::where('branch_id', $branch->id)->where('quantity', 0)->count();
            $branch->total_stock = Inventory::where('branch_id', $branch->id)->sum('quantity');
            return $branch;
        });
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created branch in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
        ]);

        Branch::create([
            'branch_name' => $request->branch_name,
            'location'    => $request->location,
        ]);

        return redirect()->route('branches.index')->with('success', '✅ Branch created successfully.');
    }

    /**
     * Show the form for editing an existing branch.
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch in the database.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
        ]);

        $branch->update([
            'branch_name' => $request->branch_name,
            'location'    => $request->location,
        ]);

        return redirect()->route('branches.index')->with('success', '✅ Branch updated successfully.');
    }

    /**
     * Remove the specified branch from the database.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', '✅ Branch deleted successfully.');
    }
}