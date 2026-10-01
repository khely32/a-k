<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_merge_logs')) {
            Schema::create('product_merge_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('canonical_product_id');
                $table->string('canonical_serial_number')->nullable();
                $table->unsignedBigInteger('merged_product_id');
                $table->string('merged_serial_number')->nullable();
                $table->integer('inventories_merged')->default(0);
                $table->integer('sale_items_repointed')->default(0);
                $table->integer('stock_transfers_repointed')->default(0);
                $table->timestamps();

                $table->index('canonical_product_id');
                $table->index('merged_product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_merge_logs');
    }
};