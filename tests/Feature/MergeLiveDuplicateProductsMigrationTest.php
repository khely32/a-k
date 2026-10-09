<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MergeLiveDuplicateProductsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_10_09_000001_merge_live_duplicate_products';

    /**
     * [id => [name, brand, color, type, size, price]] - the rows read from
     * /products/{id}/edit on the live site.
     */
    private const LIVE_IDENTITIES = [
        32  => ['Bosny Spray Paint', 'Bosny', 'Flat Black', 'Lubricants & Maintenance', '400cc / 300g', 160],
        87  => ['Bosny Spray Paint', 'Bosny', 'Flat Black', 'Lubricants & Maintenance', '400ml', 160],
        36  => ['Bosny Spray Paint - Grass Green', 'Bosny', 'Grass Green', 'Lubricants & Maintenance', '', 160],
        91  => ['Bosny Spray Paint - Grass Green', 'Bosny', 'Grass Green', 'Lubricants & Maintenance', '400ml', 160],
        90  => ['Bosny Spray Paint - Silver', 'Bosny', 'Silver', 'Lubricants & Maintenance', '400cc / 300g', 160],
        35  => ['Bosny Spray Paint - Silver', 'Bosny', 'Silver', 'Lubricants & Maintenance', '400ml', 165],
        104 => ['Samurai Spray Paint - Clear', 'Samurai', 'Clear', 'Lubricants & Maintenance', '400ml', 240],
        49  => ['Samurai Spray Paint - Clear', 'Samurai', 'Clear', 'Lubricants & Maintenance', '400lml', 240],
        120 => ['Tubeless Tire Valve Stems & Repair Kit', 'Universal', '', 'Mirrors & Accessories', 'Standard', 70],
        124 => ['Tubeless Tire Valve Stems & Repair Kit', 'Universal', '', 'Universal', 'Standard', 75],
        172 => ['Stainless Steel Spoke & Nipple Set', 'Osaki / Chrome King', 'Chrome / Gold', 'Tires & Inner Tubes', '9 x 89 / 9 x 157 / 9 x 184', 380],
        173 => ['Stainless Steel Spoke & Nipple Set', 'Stainless Steel Spoke & Nipple Set', '', 'Wheels & Rims', '(e.g., volume/viscosity/dimensions): 9 x 89 / 9 x 157 / 9 x 184', 380],
        28  => ['Koby Premium Acrylic Spray Paint', 'Koby', 'Suzuki Red', 'Lubricants & Maintenance', '450ml', 130],
        81  => ['Koby Premium Acrylic Spray Paint', 'Koby', 'Suzuki Red', 'Lubricants & Maintenance', '400ml', 130],
        29  => ['Koby Premium Acrylic Spray Paint', 'Koby', '18K Gold', 'Lubricants & Maintenance', '450ml', 135],
        82  => ['Koby Premium Acrylic Spray Paint', 'Koby', '18K Gold', 'Lubricants & Maintenance', '400ml', 130],
    ];

    private const KEEP_IDS = [32, 36, 90, 104, 120, 172, 28, 29];

    private const DUP_IDS = [87, 91, 35, 49, 124, 173, 81, 82];

    public function test_running_the_migration_merges_exactly_the_live_pairs(): void
    {
        $branch = Branch::create(['branch_name' => 'Moroboro Branch', 'location' => 'X', 'is_active' => true]);

        foreach (static::LIVE_IDENTITIES as $id => [$name, $brand, $color, $type, $size, $price]) {
            $product = new Product();
            $product->id = $id;
            $product->name = $name;
            $product->brand = $brand;
            $product->color = $color;
            $product->type = $type;
            $product->size = $size;
            $product->price = $price;
            $product->quantity = 500;
            $product->save();
        }

        // Give two of the doomed rows some stock so the merge has work to do.
        $branch->inventories()->where('product_id', 87)->update(['quantity' => 7]);
        $branch->inventories()->where('product_id', 49)->update(['quantity' => 3]);

        // Mark the migration unapplied and run only it.
        DB::table('migrations')->where('migration', static::MIGRATION)->delete();
        Artisan::call('migrate', ['--path' => 'database/migrations/'.static::MIGRATION.'.php', '--force' => true]);

        // Surviving keeps are untouched; the 8 duplicates are gone.
        $expectedKeeps = collect(static::KEEP_IDS)->sort()->values()->all();
        $this->assertSame($expectedKeeps, Product::orderBy('id')->pluck('id')->all());
        foreach (static::DUP_IDS as $id) {
            $this->assertDatabaseMissing('products', ['id' => $id]);
        }

        // Stock and quantity from a duplicate landed on its keep.
        $this->assertDatabaseHas('inventories', ['product_id' => 32, 'branch_id' => $branch->id, 'quantity' => 7]);
        $this->assertDatabaseHas('inventories', ['product_id' => 104, 'branch_id' => $branch->id, 'quantity' => 3]);

        // Every merge was audited.
        foreach (static::DUP_IDS as $id) {
            $this->assertDatabaseHas('product_merge_logs', ['merged_product_id' => $id]);
        }
    }

    public function test_migration_refuses_to_merge_when_rows_no_longer_match(): void
    {
        $branch = Branch::create(['branch_name' => 'Moroboro Branch', 'location' => 'X', 'is_active' => true]);

        foreach (static::LIVE_IDENTITIES as $id => [$name, $brand, $color, $type, $size, $price]) {
            $product = new Product();
            $product->id = $id;
            $product->name = $name;
            $product->brand = $brand;
            $product->color = $color;
            $product->type = $type;
            $product->size = $size;
            $product->price = $price;
            $product->quantity = 500;
            $product->save();
        }

        // Drift the duplicate away from the identity that was inspected and
        // re-classify it as a different product (e.g. the keeper holds the
        // only remaining claim to the name).
        Product::whereIn('id', static::DUP_IDS)
            ->update(['name' => 'A Different Product']);

        DB::table('migrations')->where('migration', static::MIGRATION)->delete();

        $this->expectException(\RuntimeException::class);

        Artisan::call('migrate', ['--path' => 'database/migrations/'.static::MIGRATION.'.php', '--force' => true]);
    }
}