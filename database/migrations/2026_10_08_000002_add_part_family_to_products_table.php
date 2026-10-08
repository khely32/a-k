<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Support\PartFamily;

/**
 * Add `products.part_family` - the motorcycle part a product IS (Tire,
 * Spark Plug, Oil Filter, ...), as opposed to the broad system it belongs
 * to (`category`, e.g. Engine Parts).
 *
 * Part-level detail used to live in `type` until the category normalisation
 * collapsed it, so the Master Products dropdown could only offer the ten
 * broad groups. This column is derived from the product name (then its
 * category) against the curated `category_sizes` list and is kept in step by
 * the Product saving hook.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        if (! Schema::hasColumn('products', 'part_family')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('part_family')->nullable()->after('category');
                $table->index('part_family');
            });
        }

        $rows = DB::table('products')->select(['id', 'name', 'category'])->get();

        foreach ($rows as $row) {
            $partFamily = PartFamily::resolve($row->name, $row->category);

            if ($partFamily === null) {
                continue;
            }

            DB::table('products')->where('id', $row->id)->update(['part_family' => $partFamily]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'part_family')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['part_family']);
                $table->dropColumn('part_family');
            });
        }
    }
};
