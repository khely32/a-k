<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class MotorOilProductsBatch3Seeder extends Seeder
{
    protected $moroboroBranchId = 8;

    protected $products = [
        ['name' => 'Castrol Activ Essential SAE 40 Motorcycle Engine Oil', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Castrol Activ 4T 20W-40 Motorcycle Engine Oil', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 230.00],
        ['name' => 'Honda 4T SJ 40 MA Motorcycle Engine Oil', 'brand' => 'Honda', 'type' => 'Engine Oil', 'price' => 250.00],
        ['name' => 'TOP 1 Action Matic', 'brand' => 'TOP 1', 'type' => 'ATF', 'price' => 180.00],
        ['name' => 'TOP 1 Action Plus Motorcycle Oil', 'brand' => 'TOP 1', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'TOP 1 MC SAE 20W-50 JASO MA2 Motorcycle Engine Oil', 'brand' => 'TOP 1', 'type' => 'Engine Oil', 'price' => 230.00],
        ['name' => 'TOP 1 Synthetic Motor Oil SAE 20W-50 JASO MA2', 'brand' => 'TOP 1', 'type' => 'Engine Oil', 'price' => 280.00],
        ['name' => 'Yamalube RS500 10W-40 4T Motorcycle Engine Oil', 'brand' => 'Yamalube', 'type' => 'Engine Oil', 'price' => 300.00],
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
