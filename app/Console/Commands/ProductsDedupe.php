<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\SaleItem;
use App\Models\StockTransfer;
use App\Models\ProductMergeLog;
use Illuminate\Support\Facades\DB;

class ProductsDedupe extends Command
{
    protected $signature = 'products:dedupe {--dry-run : Report duplicates without merging}';

    protected $description = 'Merge duplicate products (same name + brand + type + size + color) into a single record';

    public function handle(): int
    {
        $products = Product::orderBy('id')->get();

        $groups = [];
        foreach ($products as $product) {
            $key = strtolower(trim((string) $product->name))
                . '|' . strtolower(trim((string) $product->brand))
                . '|' . strtolower(trim((string) $product->type))
                . '|' . strtolower(trim((string) $product->size))
                . '|' . strtolower(trim((string) $product->color));
            $groups[$key][] = $product;
        }

        $suspectedDuplicates = 0;
        $merged = 0;

        DB::beginTransaction();

        try {
            foreach ($groups as $items) {
                if (count($items) < 2) {
                    continue;
                }

                usort($items, fn ($a, $b) => $a->id <=> $b->id);

                $canonical = array_shift($items);
                $suspectedDuplicates += count($items);

                $this->line(sprintf(
                    'Merging %d duplicate(s) of "%s" -> keep #%d (Serial: %s)',
                    count($items),
                    $canonical->name,
                    $canonical->id,
                    $canonical->serial_number ?? '(none)'
                ));

                foreach ($items as $dup) {
                    $this->line(sprintf(
                        '    #%d (Serial: %s) -> #%d (Serial: %s)',
                        $dup->id,
                        $dup->serial_number ?? '(none)',
                        $canonical->id,
                        $canonical->serial_number ?? '(none)'
                    ));

                    if ($this->option('dry-run')) {
                        continue;
                    }

                    $inventoriesMerged = 0;
                    foreach ($dup->inventories as $inv) {
                        $target = Inventory::firstOrNew([
                            'product_id' => $canonical->id,
                            'branch_id'  => $inv->branch_id,
                        ]);
                        $target->quantity = ($target->exists ? (int) $target->quantity : 0) + (int) $inv->quantity;
                        $target->save();
                        $inventoriesMerged++;
                    }

                    $saleItems = SaleItem::where('product_id', $dup->id)->update(['product_id' => $canonical->id]);
                    $transfers = StockTransfer::where('product_id', $dup->id)->update(['product_id' => $canonical->id]);

                    $canonical->quantity = (int) $canonical->quantity + (int) $dup->quantity;

                    // The duplicate row is about to disappear, so record where
                    // its serial number went before that happens.
                    ProductMergeLog::create([
                        'canonical_product_id'      => $canonical->id,
                        'canonical_serial_number'   => $canonical->serial_number,
                        'merged_product_id'         => $dup->id,
                        'merged_serial_number'      => $dup->serial_number,
                        'inventories_merged'        => $inventoriesMerged,
                        'sale_items_repointed'      => $saleItems,
                        'stock_transfers_repointed' => $transfers,
                    ]);

                    $dup->delete();
                    $merged++;
                }

                if (!$this->option('dry-run')) {
                    $canonical->save();
                }
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
                $groupCount = collect($groups)->filter(fn ($g) => count($g) >= 2)->count();
                $this->info("Dry run: found {$suspectedDuplicates} duplicate product(s) across {$groupCount} group(s). Nothing changed.");

                return Command::SUCCESS;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Merge failed and was rolled back: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $this->info("Done. Merged {$merged} duplicate product(s). Discarded serial numbers were recorded in product_merge_logs.");

        return Command::SUCCESS;
    }
}