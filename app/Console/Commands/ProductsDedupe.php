<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Support\ProductMerger;
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

                    ProductMerger::merge($canonical, $dup);
                    $merged++;
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