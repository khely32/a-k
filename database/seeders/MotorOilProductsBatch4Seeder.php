<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class MotorOilProductsBatch4Seeder extends Seeder
{
    protected $moroboroBranchId;

    protected $products = [
        ['name' => 'Shell Helix HX3 SAE 30', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 250.00],
        ['name' => 'Shell Advance AX3 20W-40', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 230.00],
        ['name' => 'Shell Advance Power 15W-50', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 350.00],
        ['name' => 'Petron Blaze Racing BR200 SAE 20W-40', 'brand' => 'Petron', 'type' => 'Engine Oil', 'price' => 250.00],
        ['name' => 'Shercar Super Coolant 3-in-1', 'brand' => 'Shercar', 'type' => 'Coolant', 'price' => 150.00],
    ];

    public function run(): void
    {
        $this->moroboroBranchId = \App\Models\Branch::whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])->value('id');
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
