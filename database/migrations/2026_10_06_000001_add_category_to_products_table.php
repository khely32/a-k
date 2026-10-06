<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Support\ProductCategory;

/**
 * Add the normalised `products.category` column.
 *
 * `products.type` was doing two jobs at once: a top-level grouping
 * ("Lubricant", "Electrical") AND a variant/size code ("Std", "C1",
 * "0.25"). That is what produced the duplicated POS dropdown options
 * (Lubricant / Lubricants / Lubricants / Gear Oil). Rewriting `type`
 * itself would lose the variant data that Product::findDuplicate() relies
 * on, so the two concerns get separate columns instead: `type` stays as-is,
 * `category` holds the canonical taxonomy from ProductCategory.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('category')->nullable()->after('type');
            });
        }

        if (! Schema::hasTable('products')) {
            return;
        }

        $products = \App\Models\Product::query()
            ->select(['id', 'type', 'name'])
            ->get();

        foreach ($products as $product) {
            $category = ProductCategory::resolve($product->type, $product->name);

            // Bypass Eloquent so the model's saving hook cannot re-derive
            // while we are already applying the derived value.
            \App\Models\Product::where('id', $product->id)
                ->update(['category' => $category]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->index('category');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            });
        }
    }
};
