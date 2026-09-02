<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // ✅ Branch name
            $table->string('address');    // ✅ Branch address
            $table->timestamps();
        });

        // ✅ YOUR EXACT 4 BRANCHES from your capstone document
        DB::table('branches')->insert([
            [
                'name'       => 'Brgy. Moroboro',
                'address'    => 'Brgy Moroboro, Dingle, Iloilo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name'       => 'Poblacion Muyco St.',
                'address'    => 'Brgy. Poblacion Muyco St., Dingle, Iloilo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name'       => 'Brgy. San Matias',
                'address'    => 'Brgy. San Matias, Dingle, Iloilo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name'       => 'Bularan St.',
                'address'    => 'Bularan St., Banate, Iloilo',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};