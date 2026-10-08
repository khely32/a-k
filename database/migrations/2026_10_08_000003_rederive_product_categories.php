<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Support\PartFamily;
use App\Support\ProductCategory;

/**
 * Re-file existing products under the expanded category taxonomy.
 *
 * The dropdown used to offer ten groups, so everything the owner typed when
 * adding a product - "Fuel System & Air Intake", "Exhaust & Emissions",
 * "Cooling System", "Handlebars & Controls", ... - was resolved away into a
 * broader bucket and could never be selected as a filter. Those groups are
 * categories now, and `wheel`/`rim`/`spoke` no longer belong to Tires & Inner
 * Tubes, so every stored row is re-derived.
 *
 * Both `category` and `part_family` are recomputed exactly the way the
 * Product saving hook does it, so nothing can drift from what a fresh save
 * would produce.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $hasPartFamily = Schema::hasColumn('products', 'part_family');

        $rows = DB::table('products')
            ->select(array_filter(['id', 'type', 'name', 'category', 'part_family'], fn ($column) => $column !== 'part_family' || $hasPartFamily))
            ->get();

        foreach ($rows as $row) {
            $category = ProductCategory::resolve($row->type, $row->name);
            $partFamily = $hasPartFamily ? PartFamily::resolve($row->name, $category) : null;

            $update = [];

            if ($category !== $row->category) {
                $update['category'] = $category;
            }

            if ($hasPartFamily && $partFamily !== $row->part_family) {
                $update['part_family'] = $partFamily;
            }

            if ($update === []) {
                continue;
            }

            DB::table('products')->where('id', $row->id)->update($update);
        }
    }

    public function down(): void
    {
        // The old grouping was derived data as well, and re-deriving it
        // would need the previous rules back - not worth it.
    }
};
