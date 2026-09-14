<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class LubricantAccessoryProductsSeeder extends Seeder
{
    protected $mainBranchId;

    protected $products = [
        // ── Lubricants ─────────────────────────────────────────────
        ['name' => 'Unioil Motosport 4T Scooter 10W-30 Engine Oil',   'brand' => 'Unioil',  'type' => 'Engine Oil',  'price' => 180.00],
        ['name' => 'Unioil SMO SAE 40 Motorcycle Engine Oil',          'brand' => 'Unioil',  'type' => 'Engine Oil',  'price' => 170.00],
        ['name' => 'RS8 100% Synthetic 10W-40 Motorcycle Engine Oil',  'brand' => 'RS8',     'type' => 'Engine Oil',  'price' => 350.00],
        ['name' => 'Suretite Black RTV Silicone Sealant',              'brand' => 'Suretite','type' => 'Accessories', 'price' => 150.00],

        // ── Levers / Accessories ───────────────────────────────────
        ['name' => 'Ahim Aerox Red CNC Brake Lever',                  'brand' => 'Ahim',    'type' => 'Lever',  'price' => 700.00],
        ['name' => 'GZL Click Orange Brake Lever',                    'brand' => 'GZL',     'type' => 'Lever',  'price' => 500.00],
        ['name' => 'Domino Gold Brake Lever',                         'brand' => 'Domino',  'type' => 'Lever',  'price' => 450.00],
        ['name' => 'Option-1 Brake Lever',                            'brand' => 'Option-1','type' => 'Lever',  'price' => 350.00],
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
            $this->command->info('Lubricants & Accessories: created ' . count($created) . ' product(s).');
            foreach ($skipped as $name) {
                $this->command->warn('Skipped (exists): ' . $name);
            }
        }
    }
}