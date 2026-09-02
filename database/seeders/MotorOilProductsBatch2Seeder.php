<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class MotorOilProductsBatch2Seeder extends Seeder
{
    protected $moroboroBranchId = 8;

    protected $products = [
        ['name' => 'Slick Road Runner SAE 40 Motorcycle Engine Oil', 'brand' => 'Slick Lubricants', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Slick Road Runner SF 20W-40 Motorcycle Engine Oil', 'brand' => 'Slick Lubricants', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Slick 4T Super Transporter SJ/CF 20W-50 Motorcycle Engine Oil', 'brand' => 'Slick Lubricants', 'type' => 'Engine Oil', 'price' => 230.00],
        ['name' => 'Slick 4T Super Scooter SL/MB 10W-40 Motorcycle Engine Oil', 'brand' => 'Slick Lubricants', 'type' => 'Engine Oil', 'price' => 250.00],
        ['name' => 'Petron Sprint 4T Motorcycle Engine Oil', 'brand' => 'Petron', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Shell Advance 2T', 'brand' => 'Shell', 'type' => '2-Stroke Oil', 'price' => 120.00],
        ['name' => 'Shell Advance SX2 Triple 2T Motorcycle Oil', 'brand' => 'Shell', 'type' => '2-Stroke Oil', 'price' => 140.00],
        ['name' => 'Petron 2T Powerburn Motorcycle Oil', 'brand' => 'Petron', 'type' => '2-Stroke Oil', 'price' => 130.00],
        ['name' => 'Unioil Motosport 800 SAE 40 Motorcycle Engine Oil', 'brand' => 'Unioil', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Unioil 4T 10W-30 Motorcycle Engine Oil', 'brand' => 'Unioil', 'type' => 'Engine Oil', 'price' => 220.00],
        ['name' => 'Chevron Delo Gold 15W-40 Engine Oil', 'brand' => 'Chevron', 'type' => 'Engine Oil', 'price' => 350.00],
        ['name' => 'Caltex Havoline 4T 20W-40 Motorcycle Engine Oil', 'brand' => 'Caltex', 'type' => 'Engine Oil', 'price' => 260.00],
        ['name' => 'Unioil SMO SAE 40 Engine Oil', 'brand' => 'Unioil', 'type' => 'Engine Oil', 'price' => 220.00],
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
