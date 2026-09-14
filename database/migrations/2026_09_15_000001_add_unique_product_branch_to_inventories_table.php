<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce the one-row-per-(product, branch) rule on `inventories`.
 *
 * Prior deploys could create duplicate rows for the same (product_id, branch_id)
 * because there was no unique constraint and some code paths used raw `create()`.
 * A unique index is required for `updateOrCreate()`/`firstOrCreate()` to behave
 * safely, and it also matches the master/Branch inventory model:
 * a product has EXACTLY ONE quantity per branch.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Collapse any pre-existing duplicate rows, keeping the lowest id.
        // Postgres-compatible, tested against Neon.
        DB::statement('
            DELETE FROM inventories a
            USING inventories b
            WHERE a.product_id = b.product_id
              AND a.branch_id = b.branch_id
              AND a.id > b.id
        ');

        Schema::table('inventories', function (Blueprint $table) {
            $table->unique(['product_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(['inventories_product_id_branch_id_unique']);
        });
    }
};