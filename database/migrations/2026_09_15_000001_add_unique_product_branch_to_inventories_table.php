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
        //
        // Written as a NOT IN (SELECT MIN(id) ...) subquery rather than
        // "DELETE ... USING": the USING form is Postgres-only and blew up on
        // the SQLite connection the test suite runs on, which silently made
        // every RefreshDatabase test error out before it started.
        DB::statement('
            DELETE FROM inventories
            WHERE id NOT IN (
                SELECT MIN(id) FROM inventories GROUP BY product_id, branch_id
            )
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