<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductMergeLog;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockTransfer;
use App\Support\ProductMerger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMergerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_transfers_stock_and_repoints_history_then_deletes_the_duplicate(): void
    {
        $branchA = Branch::create(['branch_name' => 'Branch A', 'location' => 'A', 'is_active' => true]);
        $branchB = Branch::create(['branch_name' => 'Branch B', 'location' => 'B', 'is_active' => true]);

        $canonical = Product::create([
            'name' => 'Bosny Spray Paint', 'brand' => 'Bosny',
            'type' => 'Lubricants & Maintenance', 'color' => 'Flat Black',
            'size' => '400cc / 300g', 'quantity' => 10, 'price' => 160,
        ]);
        $duplicate = Product::create([
            'name' => 'Bosny Spray Paint', 'brand' => 'Bosny',
            'type' => 'Lubricants & Maintenance', 'color' => 'Flat Black',
            'size' => '400ml', 'quantity' => 5, 'price' => 160,
        ]);

        // The creating hook already made a row per active branch; give them
        // the quantities we want the merge to fold together.
        $canonical->inventories()->where('branch_id', $branchA->id)->update(['quantity' => 10]);
        $canonical->inventories()->where('branch_id', $branchB->id)->update(['quantity' => 0]);
        $duplicate->inventories()->where('branch_id', $branchA->id)->update(['quantity' => 4]);
        $duplicate->inventories()->where('branch_id', $branchB->id)->update(['quantity' => 6]);

        $sale = Sale::create(['total_amount' => 300, 'payment_method' => 'cash']);
        $saleItem = SaleItem::create(['sale_id' => $sale->id, 'product_id' => $duplicate->id, 'quantity' => 2, 'price' => 150]);
        $transfer = StockTransfer::create([
            'product_id' => $duplicate->id,
            'from_branch_id' => $branchA->id,
            'to_branch_id' => $branchB->id,
            'quantity' => 3,
            'status' => 'approved',
        ]);

        ProductMerger::merge($canonical, $duplicate);

        $this->assertDatabaseMissing('products', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('products', ['id' => $canonical->id, 'quantity' => 15]);
        $this->assertDatabaseHas('inventories', ['product_id' => $canonical->id, 'branch_id' => $branchA->id, 'quantity' => 14]);
        $this->assertDatabaseHas('inventories', ['product_id' => $canonical->id, 'branch_id' => $branchB->id, 'quantity' => 6]);

        $this->assertDatabaseHas('sale_items', ['id' => $saleItem->id, 'product_id' => $canonical->id]);
        $this->assertDatabaseHas('stock_transfers', ['id' => $transfer->id, 'product_id' => $canonical->id]);

        $this->assertDatabaseHas('product_merge_logs', [
            'canonical_product_id' => $canonical->id,
            'merged_product_id' => $duplicate->id,
            'inventories_merged' => 2,
            'sale_items_repointed' => 1,
            'stock_transfers_repointed' => 1,
        ]);
    }

    public function test_unrelated_products_are_left_alone(): void
    {
        $branch = Branch::create(['branch_name' => 'Branch A', 'location' => 'A', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Engine Oil', 'brand' => 'Honda',
            'type' => 'Lubricants & Maintenance', 'color' => '', 'size' => '1L',
            'quantity' => 3, 'price' => 200,
        ]);

        // There is no duplicate row for it - a fresh find must still exist.
        $this->assertDatabaseCount('products', 1);
        $this->assertSame(3, (int) $product->fresh()->quantity);
        $this->assertDatabaseMissing('product_merge_logs', ['merged_product_id' => $product->id]);
    }
}