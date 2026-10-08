<?php

use App\Support\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collapse the legacy category spellings the Master Products filter used to
 * list as separate options:
 *
 *   Tire / Tires / Rear Shock Absorber / Engine Part / Electrical /
 *   Maintenance Sprays / Cleaning Supplies / Spray Paint / Maintenance
 *     -> the canonical category each one belongs to.
 *
 * `products.type` doubles as a variant/size code ("Std", "C1", "0.25"), so
 * only values that canonicalise() recognises as a category are rewritten;
 * a genuine variant code is left exactly as it was. `products.category` is
 * always re-derived from the resulting type + name, which is what the
 * dropdown reads, so the filter stays clean even for rows whose `type`
 * cannot be normalised.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $rows = DB::table('products')->select(['id', 'type', 'name', 'category'])->get();

        foreach ($rows as $row) {
            $type = $row->type === null ? null : trim($row->type);

            // A legacy category spelling collapses onto its canonical form;
            // anything else (variant codes, part numbers) is kept as-is.
            $normalisedType = ProductCategory::canonicalise($type) ?? $type;
            $category = ProductCategory::resolve($normalisedType, $row->name);

            if ($normalisedType === $row->type && $category === $row->category) {
                continue;
            }

            // Query builder, not Eloquent: the model's saving hook would
            // re-derive the same values we are already applying.
            DB::table('products')->where('id', $row->id)->update([
                'type' => $normalisedType,
                'category' => $category,
            ]);
        }
    }

    public function down(): void
    {
        // Data normalisation is a one-way street: the legacy spellings are
        // not recoverable (and are not wanted back).
    }
};
