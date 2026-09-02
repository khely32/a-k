<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class InventoryBatch5Seeder extends Seeder
{
    protected $moroboroBranchId;

    protected $products = [
        ['name' => 'Castrol Activ Scooter 10W-40 4-AT (1L)', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 285.00],
        ['name' => 'Castrol Power1 Scooter 10W-40 4-AT (1L)', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 330.00],
        ['name' => 'Castrol Power1 10W-40 4T (1L)', 'brand' => 'Castrol', 'type' => 'Engine Oil', 'price' => 325.00],
        ['name' => 'Shell Brake & Clutch Fluid DOT 3 (1L)', 'brand' => 'Shell', 'type' => 'Brake Fluid', 'price' => 320.00],
        ['name' => 'Sure Brake Heavy Duty DOT 3 Brake Fluid (900ml)', 'brand' => 'Sure Brake', 'type' => 'Brake Fluid', 'price' => 195.00],
        ['name' => 'Sure Brake Heavy Duty DOT 3 Brake Fluid (300ml)', 'brand' => 'Sure Brake', 'type' => 'Brake Fluid', 'price' => 95.00],
        ['name' => 'Yamalube Motorcycle Gear Oil (100ml)', 'brand' => 'Yamalube', 'type' => 'Gear Oil', 'price' => 90.00],
        ['name' => 'Petron Scooter Gear Oil (100ml)', 'brand' => 'Petron', 'type' => 'Gear Oil', 'price' => 85.00],
        ['name' => 'Singe All-Purpose Oil', 'brand' => 'Singe', 'type' => 'Lubricant', 'price' => 40.00],
        ['name' => 'TOP 1 Engine Oil Treatment', 'brand' => 'TOP 1', 'type' => 'Engine Additive', 'price' => 195.00],
        ['name' => 'Koby De-Rust Lubricating Spray (450ml)', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 175.00],
        ['name' => 'Koby VS1 Protector Spray (450ml)', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 185.00],
        ['name' => 'Koby Sticker Remover (450ml)', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 165.00],
        ['name' => 'Koby Helmet Disinfecting Foam Cleaner', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 170.00],
        ['name' => 'Koby Paint Remover (450ml)', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 180.00],
        ['name' => 'Koby Carburetor & Choke Cleaner (450ml)', 'brand' => 'Koby', 'type' => 'Spray', 'price' => 155.00],
        ['name' => 'Sher1 Chain Cleaner & Degreaser', 'brand' => 'Sher1', 'type' => 'Spray', 'price' => 160.00],
        ['name' => 'RS CVT Clean Degreaser', 'brand' => 'RS', 'type' => 'Spray', 'price' => 175.00],
        ['name' => 'Sherca Metal Polish Spray', 'brand' => 'Sherca', 'type' => 'Spray', 'price' => 150.00],
        ['name' => 'Motolite Motorcycle Battery (MCB Series)', 'brand' => 'Motolite', 'type' => 'Battery', 'price' => 1150.00],
        ['name' => 'Quantum Premium Motorcycle Battery', 'brand' => 'Quantum', 'type' => 'Battery', 'price' => 850.00],
        ['name' => 'Super Suretite Super Glue (Card Pack)', 'brand' => 'Suretite', 'type' => 'Accessory', 'price' => 135.00],
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
