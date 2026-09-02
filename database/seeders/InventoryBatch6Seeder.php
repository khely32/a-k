<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class InventoryBatch6Seeder extends Seeder
{
    protected $moroboroBranchId = 8;

    protected $products = [
        ['name' => 'Shell Rimula R4 X 15W-40 Diesel Engine Oil (6L Promo Pack)', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 1750.00],
        ['name' => 'Petron GEP Extreme Pressure Automotive Gear Oil SAE 90 (4L)', 'brand' => 'Petron', 'type' => 'Gear Oil', 'price' => 720.00],
        ['name' => 'Koby Tire Sealer & Inflator Spray', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 180.00],
        ['name' => 'Koby Auto Tyre Sealer Liquid', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 135.00],
        ['name' => 'Koby Chain Lube Spray', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 165.00],
        ['name' => 'Wangle Rust Remover Spray (100ml)', 'brand' => 'Wangle', 'type' => 'Spray', 'price' => 85.00],
    ];

    public function run(): void
    {
        $created = [];
        $skipped = [];

        foreach ($this->products as $item) {
            $product = Product::firstOrNew([
                'name' => $item['name'],
                'brand' => $item['brand'],
            ]);

            if ($product->exists) {
                $skipped[] = $item['name'];
                continue;
            }

            $product->type = $item['type'];
            $product->price = $item['price'];
            $product->quantity = 500;
            $product->branch_id = $this->moroboroBranchId;
            $product->save();

            Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'branch_id'  => $this->moroboroBranchId,
                ],
                ['quantity' => 500]
            );

            $created[] = $item['name'];
        }

        if ($this->command) {
            $this->command->info('Created ' . count($created) . ' product(s) at Moroboro Branch.');
            foreach ($skipped as $name) {
                $this->command->warn('Already exists — skipped duplicate: ' . $name);
            }
        }
    }
}
