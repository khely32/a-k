<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\CategorySize;

class ProductController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $mainBranch = Branch::whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])
            ->orWhereRaw('LOWER(branch_name) LIKE ?', ['%branch 1%'])
            ->first();
        $isMainBranch = $mainBranch && $user->branch_id == $mainBranch->id;

        if ($isMainBranch) {
            $branches = Branch::orderBy('id', 'asc')->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        $products = Product::whereHas('inventories')
            ->orderBy('name', 'asc')
            ->get();

        $inventories = Inventory::all();
        $inventoryMap = [];
        foreach ($inventories as $inv) {
            $inventoryMap[$inv->product_id][$inv->branch_id] = $inv->quantity;
        }

        $totalInventoryValue = 0;

        foreach ($products as $product) {
            $branchStock = [];
            $totalStock = 0;

            foreach ($branches as $branch) {
                $qty = $inventoryMap[$product->id][$branch->id] ?? 0;
                $branchStock[$branch->id] = $qty;
                $totalStock += $qty;
            }

            $product->branch_stock = $branchStock;
            $product->total_stock = $totalStock;
            $totalInventoryValue += $totalStock * $product->price;
        }

        return view('products.index', compact('products', 'branches', 'totalInventoryValue', 'isMainBranch'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:255',
            'size'        => 'nullable|string|max:255',
            'quantity'    => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        $product = Product::create([
            'name'        => $request->name,
            'brand'       => $request->brand,
            'type'        => $request->type,
            'color'       => $request->color,
            'size'        => $request->size,
            'quantity'    => $request->quantity,
            'price'       => $request->price,
            'description' => $request->description,
            'branch_id'   => $branchId,
        ]);

        Inventory::create([
            'product_id' => $product->id,
            'branch_id'  => $branchId,
            'quantity'   => $request->quantity,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:255',
            'size'        => 'nullable|string|max:255',
            'quantity'    => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name'        => $request->name,
            'brand'       => $request->brand,
            'type'        => $request->type,
            'color'       => $request->color,
            'size'        => $request->size,
            'quantity'    => $request->quantity,
            'price'       => $request->price,
            'description' => $request->description,
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        Inventory::updateOrCreate(
            [
                'product_id' => $product->id,
                'branch_id'  => $branchId,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function getSizes($category)
    {
        $sizes = CategorySize::where('category', $category)
            ->orderBy('sort_order')
            ->pluck('size');
        return response()->json($sizes);
    }

    /**
     * Import products from a CSV file in bulk.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open the uploaded file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'The CSV file is empty or invalid.');
        }

        // Clean headers and find mappings
        $headerMap = [];
        foreach ($header as $index => $col) {
            $colClean = strtolower(trim($col));
            $headerMap[$colClean] = $index;
        }

        // Standard mapping configurations
        $mappings = [
            'name' => ['name', 'product name', 'item description', 'part name'],
            'brand' => ['brand', 'manufacturer', 'make'],
            'type' => ['type', 'category', 'class'],
            'color' => ['color', 'colour', 'shade'],
            'quantity' => ['quantity', 'qty', 'stock', 'stock level', 'stock quantity'],
            'price' => ['price', 'retail price', 'rate', 'selling price'],
            'serial_number' => ['serial number', 'serial_number', 'sku', 'code', 'serial', 'item code'],
            'size' => ['size', 'sizes', 'volume', 'capacity'],
            'description' => ['description', 'remarks', 'notes', 'details'],
        ];

        // Find which indexes match our database fields
        $fields = [];
        foreach ($mappings as $dbField => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($headerMap[$alias])) {
                    $fields[$dbField] = $headerMap[$alias];
                    break;
                }
            }
        }

        // Validate critical fields
        if (!isset($fields['name'])) {
            fclose($handle);
            return back()->with('error', 'Required column "Name" (or "Part Name", "Item Description") was not found in the CSV header.');
        }
        if (!isset($fields['price'])) {
            fclose($handle);
            return back()->with('error', 'Required column "Price" (or "Rate", "Selling Price") was not found in the CSV header.');
        }

        $insertedCount = 0;
        $updatedCount = 0;
        $errors = [];
        $rowCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Extract values based on mapped headers
            $name = isset($fields['name']) && isset($row[$fields['name']]) ? trim($row[$fields['name']]) : null;
            $brand = isset($fields['brand']) && isset($row[$fields['brand']]) ? trim($row[$fields['brand']]) : null;
            $type = isset($fields['type']) && isset($row[$fields['type']]) ? trim($row[$fields['type']]) : 'Uncategorized';
            $color = isset($fields['color']) && isset($row[$fields['color']]) ? trim($row[$fields['color']]) : null;
            $quantity = isset($fields['quantity']) && isset($row[$fields['quantity']]) ? intval(trim($row[$fields['quantity']])) : 0;
            $price = isset($fields['price']) && isset($row[$fields['price']]) ? floatval(trim($row[$fields['price']])) : 0.00;
            $serialNumber = isset($fields['serial_number']) && isset($row[$fields['serial_number']]) ? trim($row[$fields['serial_number']]) : null;
            $size = isset($fields['size']) && isset($row[$fields['size']]) ? trim($row[$fields['size']]) : null;
            $description = isset($fields['description']) && isset($row[$fields['description']]) ? trim($row[$fields['description']]) : null;

            if (empty($name)) {
                $errors[] = "Row {$rowCount}: Product Name is empty.";
                continue;
            }

            if ($price < 0) {
                $errors[] = "Row {$rowCount} ({$name}): Price cannot be negative.";
                continue;
            }

            if ($quantity < 0) {
                $errors[] = "Row {$rowCount} ({$name}): Stock quantity cannot be negative.";
                continue;
            }

            // Find existing product: either by serial number OR name+brand
            $existingProduct = null;
            if (!empty($serialNumber)) {
                $existingProduct = Product::where('serial_number', $serialNumber)->first();
            } else {
                $existingProduct = Product::where('name', $name)
                    ->where('brand', $brand)
                    ->first();
            }

            if ($existingProduct) {
                $existingProduct->quantity = $quantity;
                $existingProduct->price = $price;
                if (!empty($type)) {
                    $existingProduct->type = $type;
                }
                if (!empty($size)) {
                    $existingProduct->size = $size;
                }
                if (!empty($color)) {
                    $existingProduct->color = $color;
                }
                if (!empty($description)) {
                    $existingProduct->description = $description;
                }
                $existingProduct->save();

                Inventory::updateOrCreate(
                    ['product_id' => $existingProduct->id, 'branch_id' => $branchId],
                    ['quantity' => $quantity]
                );

                $updatedCount++;
            } else {
                $newProduct = new Product();
                $newProduct->name = $name;
                $newProduct->brand = $brand;
                $newProduct->type = $type;
                $newProduct->color = $color;
                $newProduct->size = $size;
                $newProduct->quantity = $quantity;
                $newProduct->price = $price;
                $newProduct->description = $description;
                if (!empty($serialNumber)) {
                    $newProduct->serial_number = $serialNumber;
                }
                $newProduct->branch_id = $branchId;
                $newProduct->save();

                Inventory::create([
                    'product_id' => $newProduct->id,
                    'branch_id'  => $branchId,
                    'quantity'   => $quantity,
                ]);

                $insertedCount++;
            }
        }

        fclose($handle);

        $statusMsg = "Import complete! Added {$insertedCount} new products, updated {$updatedCount} existing products.";
        if (count($errors) > 0) {
            $errorMsg = "Imported with some issues: " . implode(" | ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $errorMsg .= " (and " . (count($errors) - 5) . " more errors)";
            }
            return redirect()->route('products.index')->with('success', $statusMsg)->with('warning', $errorMsg);
        }

        return redirect()->route('products.index')->with('success', $statusMsg);
    }
}