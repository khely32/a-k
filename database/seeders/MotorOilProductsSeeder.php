<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class MotorOilProductsSeeder extends Seeder
{
    protected $moroboroBranchId = 8;

    protected $products = [
        ['name' => 'Castrol Activ 4T SAE 40 Motorcycle Engine Oil', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Castrol Activ 4T 20W-50 Motorcycle Engine Oil', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 230.00],
        ['name' => 'Petron GEP Extreme Pressure Gear Oil SAE 90', 'brand' => 'Petron', 'type' => 'Gear Oil', 'price' => 220.00],
        ['name' => 'Petron GEP Extreme Pressure Gear Oil SAE 140', 'brand' => 'Petron', 'type' => 'Gear Oil', 'price' => 220.00],
        ['name' => 'Petron ATF Premium HTP SAE 20', 'brand' => 'Petron', 'type' => 'ATF', 'price' => 320.00],
        ['name' => 'Petron Super Coolant Green', 'brand' => 'Petron', 'type' => 'Coolant', 'price' => 180.00],
        ['name' => 'Petron Super Coolant Pink', 'brand' => 'Petron', 'type' => 'Coolant', 'price' => 180.00],
        ['name' => 'Kawasaki Genuine Oil 4-Stroke Motorcycle Engine Oil', 'brand' => 'Kawasaki', 'type' => 'Engine Oil', 'price' => 300.00],
        ['name' => 'Pro Honda 4T Motorcycle Engine Oil', 'brand' => 'Pro Honda', 'type' => 'Engine Oil', 'price' => 280.00],
        ['name' => 'Pro Honda Scooter Motorcycle Engine Oil', 'brand' => 'Pro Honda', 'type' => 'Engine Oil', 'price' => 280.00],
        ['name' => 'Shell Advance 10W-40 Motorcycle Engine Oil', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 380.00],
        ['name' => 'Shell Advance Long Ride 10W-40 Motorcycle Engine Oil', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 450.00],
        ['name' => 'Shell Advance 15W-40 Motorcycle Engine Oil', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 350.00],
        ['name' => 'Shell Advance Fuel Save 10W-30 Motorcycle Engine Oil', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 450.00],
        ['name' => 'Shell Advance Power 20W-40 Motorcycle Engine Oil', 'brand' => 'Shell', 'type' => 'Engine Oil', 'price' => 350.00],
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
