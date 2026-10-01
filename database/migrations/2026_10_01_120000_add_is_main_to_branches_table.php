<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('branches', 'is_main')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('is_main')->default(false)->after('is_active');
            });

            DB::table('branches')
                ->whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])
                ->orWhereRaw('LOWER(branch_name) LIKE ?', ['%branch 1%'])
                ->update(['is_main' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_main');
        });
    }
};