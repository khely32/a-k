<?php

use App\Models\Product;
use App\Support\ProductMerger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the duplicate products found on the live catalog.
 *
 * Every row below was read from /products/{id}/edit on 2026-10-09. The pairs
 * share a customer-facing identity (same name, brand and colour) but were
 * entered twice - the second copy carrying a different tin-size spelling, a
 * placeholder size, a garbled brand, or a price typo - so the strict
 * name+brand+type+size+colour duplicate check never grouped them.
 *
 * Each pair is keyed by its live id and must still match the recorded
 * identity before it is touched, so this migration is a no-op on empty or
 * unrelated datasets and refuses (with a rolled-back failure) to merge
 * anything it did not inspect.
 */
return new class extends Migration
{
    /**
     * [keep id, duplicate id, identity (name/brand/color) each row must match].
     */
    private const PAIRS = [
        // Bosny Spray Paint - Flat Black, "400cc / 300g" vs "400ml".
        ['keep' => 32, 'dup' => 87,
            'keep_identity' => ['name' => 'Bosny Spray Paint', 'brand' => 'Bosny', 'color' => 'Flat Black'],
            'dup_identity'  => ['name' => 'Bosny Spray Paint', 'brand' => 'Bosny', 'color' => 'Flat Black']],

        // Bosny Spray Paint - Grass Green, blank vs "400ml".
        ['keep' => 36, 'dup' => 91,
            'keep_identity' => ['name' => 'Bosny Spray Paint - Grass Green', 'brand' => 'Bosny', 'color' => 'Grass Green'],
            'dup_identity'  => ['name' => 'Bosny Spray Paint - Grass Green', 'brand' => 'Bosny', 'color' => 'Grass Green']],

        // Bosny Spray Paint - Silver, dup priced 165 vs 160 survivor.
        ['keep' => 90, 'dup' => 35,
            'keep_identity' => ['name' => 'Bosny Spray Paint - Silver', 'brand' => 'Bosny', 'color' => 'Silver'],
            'dup_identity'  => ['name' => 'Bosny Spray Paint - Silver', 'brand' => 'Bosny', 'color' => 'Silver']],

        // Samurai Spray Paint - Clear, size typo "400lml" vs "400ml".
        ['keep' => 104, 'dup' => 49,
            'keep_identity' => ['name' => 'Samurai Spray Paint - Clear', 'brand' => 'Samurai', 'color' => 'Clear'],
            'dup_identity'  => ['name' => 'Samurai Spray Paint - Clear', 'brand' => 'Samurai', 'color' => 'Clear']],

        // Valve kit, dup priced 75 vs 70 survivor; its type also created the
        // stray "Universal" category in the dropdown.
        ['keep' => 120, 'dup' => 124,
            'keep_identity' => ['name' => 'Tubeless Tire Valve Stems & Repair Kit', 'brand' => 'Universal', 'color' => ''],
            'dup_identity'  => ['name' => 'Tubeless Tire Valve Stems & Repair Kit', 'brand' => 'Universal', 'color' => '']],

        // Spoke set, dup has the brand pasted as name and a placeholder size.
        ['keep' => 172, 'dup' => 173,
            'keep_identity' => ['name' => 'Stainless Steel Spoke & Nipple Set', 'brand' => 'Osaki / Chrome King', 'color' => 'Chrome / Gold'],
            'dup_identity'  => ['name' => 'Stainless Steel Spoke & Nipple Set', 'brand' => 'Stainless Steel Spoke & Nipple Set', 'color' => '']],

        // Koby, the "400ml" copies of the two colourways.
        ['keep' => 28, 'dup' => 81,
            'keep_identity' => ['name' => 'Koby Premium Acrylic Spray Paint', 'brand' => 'Koby', 'color' => 'Suzuki Red'],
            'dup_identity'  => ['name' => 'Koby Premium Acrylic Spray Paint', 'brand' => 'Koby', 'color' => 'Suzuki Red']],

        ['keep' => 29, 'dup' => 82,
            'keep_identity' => ['name' => 'Koby Premium Acrylic Spray Paint', 'brand' => 'Koby', 'color' => '18K Gold'],
            'dup_identity'  => ['name' => 'Koby Premium Acrylic Spray Paint', 'brand' => 'Koby', 'color' => '18K Gold']],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('product_merge_logs')) {
            return;
        }

        DB::transaction(function () {
            foreach (static::PAIRS as $pair) {
                $canonical = Product::find($pair['keep']);
                $duplicate = Product::find($pair['dup']);

                // Already gone (another environment, or handled by an earlier
                // run) - nothing to merge.
                if ($canonical === null || $duplicate === null) {
                    continue;
                }

                if (! static::identityMatches($canonical, $pair['keep_identity'])
                    || ! static::identityMatches($duplicate, $pair['dup_identity'])) {
                    throw new RuntimeException(
                        'Dedupe safety check failed for products #'.$pair['keep'].' / #'.$pair['dup']
                        .' - the rows no longer match the identities inspected live; refusing to merge.'
                    );
                }

                ProductMerger::merge($canonical, $duplicate);
            }
        });
    }

    public function down(): void
    {
        // Deliberately irreversible: the discarded rows and serial numbers are
        // recorded in product_merge_logs for audit instead of being restored.
    }

    private static function identityMatches(Product $product, array $identity): bool
    {
        foreach ($identity as $field => $expected) {
            if (strtolower(trim((string) $product->{$field})) !== strtolower(trim($expected))) {
                return false;
            }
        }

        return true;
    }
};