<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;

/**
 * Ensures every (product × active branch) pair has an `inventories` row.
 *
 * Qty is set to 0 (OUT OF STOCK) for any missing pair.  Existing rows
 * are NEVER touched, so real stock figures are preserved.
 *
 * Idempotent: safe to run repeatedly; duplicates are prevented by
 * the unique index on (product_id, branch_id).
 */
class InventoryBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::where('is_active', true)->get();

        if ($branches->isEmpty()) {
            $this->command?->warn('No active branches found – skipping inventory backfill.');
            return;
        }

        $products = Product::all();
        $created = 0;

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'branch_id'  => $branch->id,
                    ],
                    ['quantity' => 0]
                );

                if ($inventory->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $this->command?->info("Inventory backfill complete – {$created} new row(s) created for {$products->count()} product(s) × {$branches->count()} active branch(es).");
    }
}