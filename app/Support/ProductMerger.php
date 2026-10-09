<?php

namespace App\Support;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductMergeLog;
use App\Models\SaleItem;
use App\Models\StockTransfer;

/**
 * Fold one product record into another.
 *
 * The two rows share the same identity (name/brand/colour product to a
 * customer) but were entered twice - usually with a subtly different size
 * or colour spelling, which is exactly why the strict duplicate check never
 * caught them. The survivor keeps its own id, absorbs every branch's stock,
 * and takes over the merged row's sales and stock-transfer history. The
 * discarded serial number is recorded in product_merge_logs for audit, then
 * the duplicate row is hard-deleted.
 */
class ProductMerger
{
    public static function merge(Product $canonical, Product $duplicate): void
    {
        foreach ($duplicate->inventories as $inventory) {
            $target = Inventory::firstOrNew([
                'product_id' => $canonical->id,
                'branch_id'  => $inventory->branch_id,
            ]);

            $target->quantity = ($target->exists ? (int) $target->quantity : 0) + (int) $inventory->quantity;
            $target->save();
        }

        $saleItems = SaleItem::where('product_id', $duplicate->id)->update(['product_id' => $canonical->id]);
        $transfers = StockTransfer::where('product_id', $duplicate->id)->update(['product_id' => $canonical->id]);

        $canonical->quantity = (int) $canonical->quantity + (int) $duplicate->quantity;

        ProductMergeLog::create([
            'canonical_product_id'      => $canonical->id,
            'canonical_serial_number'   => $canonical->serial_number,
            'merged_product_id'         => $duplicate->id,
            'merged_serial_number'      => $duplicate->serial_number,
            'inventories_merged'        => $duplicate->inventories->count(),
            'sale_items_repointed'      => $saleItems,
            'stock_transfers_repointed' => $transfers,
        ]);

        $duplicate->delete();
        $canonical->save();
    }
}