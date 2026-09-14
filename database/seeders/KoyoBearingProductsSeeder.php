<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class KoyoBearingProductsSeeder extends Seeder
{
    protected $mainBranchId;

    protected $products = [
        ['name' => 'KOYO 6201-2RS Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '12x32x10mm',  'price' => 180.00],
        ['name' => 'KOYO 6301-2RS Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '12x37x12mm',  'price' => 220.00],
        ['name' => 'KOYO 6202-2RS Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '15x35x11mm',  'price' => 200.00],
        ['name' => 'KOYO 6302-2RS Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '15x42x13mm',  'price' => 250.00],
        ['name' => 'KOYO 6004-2RS Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '20x42x12mm',  'price' => 280.00],
        ['name' => 'KOYO 6205 C3 Bearing',   'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '25x52x15mm',  'price' => 320.00],
        ['name' => 'KOYO 63/28 C3 Bearing',  'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '28x52x12mm',  'price' => 350.00],
        ['name' => 'KOYO SAC2547 Bearing',   'brand' => 'KOYO', 'type' => 'Bearing', 'size' => '25x47x15mm',  'price' => 300.00],
    ];

    public function run(): void
    {
        $this->mainBranchId = \App\Models\Branch::whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])->value('id');
        $created = [];
        $skipped = [];

        foreach ($this->products as $item) {
            $product = Product::firstOrNew([
                'name'  => $item['name'],
                'brand' => $item['brand'],
            ]);

            if ($product->exists) {
                $skipped[] = $item['name'];
                continue;
            }

            $product->type     = $item['type'];
            $product->size     = $item['size'];
            $product->price    = $item['price'];
            $product->quantity = 500;
            $product->branch_id = $this->mainBranchId;
            $product->save();

            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $this->mainBranchId],
                ['quantity' => 500]
            );

            $created[] = $item['name'];
        }

        if ($this->command) {
            $this->command->info('Koyo Bearings: created ' . count($created) . ' product(s).');
            foreach ($skipped as $name) {
                $this->command->warn('Skipped (exists): ' . $name);
            }
        }
    }
}