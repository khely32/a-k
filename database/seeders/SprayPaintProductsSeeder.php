<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class SprayPaintProductsSeeder extends Seeder
{
    protected $mainBranchId;

    protected $products = [
        // ── Bosny Spray Paint ───────────────────────────────────────
        ['name' => 'Bosny Flat Black Spray Paint',          'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Flat Black',          'price' => 250.00],
        ['name' => 'Bosny Flat Clear Spray Paint',           'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Flat Clear',           'price' => 250.00],
        ['name' => 'Bosny Clear Spray Paint',                'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Clear',                'price' => 250.00],
        ['name' => 'Bosny Silver Spray Paint',               'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Silver',               'price' => 250.00],
        ['name' => 'Bosny Grass Green Spray Paint',          'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Grass Green',          'price' => 250.00],
        ['name' => 'Bosny Primer Grey Spray Paint',          'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Primer Grey',          'price' => 250.00],
        ['name' => 'Bosny Blue Spray Paint',                 'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Blue',                 'price' => 250.00],
        ['name' => 'Bosny Tivoli Blue Spray Paint',          'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Tivoli Blue',          'price' => 250.00],
        ['name' => 'Bosny Orange Spray Paint',               'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Orange',               'price' => 250.00],
        ['name' => 'Bosny Signal Red Spray Paint',           'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Signal Red',           'price' => 250.00],
        ['name' => 'Bosny Violet Spray Paint',               'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Violet',               'price' => 250.00],
        ['name' => 'Bosny Rose Pink Spray Paint',            'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Rose Pink',            'price' => 250.00],
        ['name' => 'Bosny Gold Spray Paint',                 'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Gold',                 'price' => 280.00],
        ['name' => 'Bosny Metallic Silver Spray Paint',      'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Metallic Silver',      'price' => 280.00],
        ['name' => 'Bosny Metallic Black Spray Paint',       'brand' => 'Bosny', 'type' => 'Spray Paint', 'color' => 'Metallic Black',       'price' => 280.00],

        // ── Samurai Spray Paint ─────────────────────────────────────
        ['name' => 'Samurai Flat Black Spray Paint',         'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Flat Black',        'price' => 260.00],
        ['name' => 'Samurai Flat Clear Spray Paint',         'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Flat Clear',        'price' => 260.00],
        ['name' => 'Samurai Clear Spray Paint',              'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Clear',             'price' => 260.00],
        ['name' => 'Samurai Silver Spray Paint',             'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Silver',            'price' => 260.00],
        ['name' => 'Samurai Grass Green Spray Paint',        'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Grass Green',       'price' => 260.00],
        ['name' => 'Samurai Primer Grey Spray Paint',        'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Primer Grey',       'price' => 260.00],
        ['name' => 'Samurai Blue Spray Paint',               'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Blue',              'price' => 260.00],
        ['name' => 'Samurai Tivoli Blue Spray Paint',        'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Tivoli Blue',       'price' => 260.00],
        ['name' => 'Samurai Orange Spray Paint',             'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Orange',            'price' => 260.00],
        ['name' => 'Samurai Signal Red Spray Paint',         'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Signal Red',        'price' => 260.00],
        ['name' => 'Samurai Violet Spray Paint',             'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Violet',            'price' => 260.00],
        ['name' => 'Samurai Rose Pink Spray Paint',          'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Rose Pink',         'price' => 260.00],
        ['name' => 'Samurai Gold Spray Paint',               'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Gold',              'price' => 290.00],
        ['name' => 'Samurai Metallic Silver Spray Paint',    'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Metallic Silver',   'price' => 290.00],
        ['name' => 'Samurai Metallic Black Spray Paint',     'brand' => 'Samurai', 'type' => 'Spray Paint', 'color' => 'Metallic Black',    'price' => 290.00],
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

            $product->type      = $item['type'];
            $product->color     = $item['color'] ?? null;
            $product->price     = $item['price'];
            $product->quantity  = 500;
            $product->branch_id = $this->mainBranchId;
            $product->save();

            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $this->mainBranchId],
                ['quantity' => 500]
            );

            $created[] = $item['name'];
        }

        if ($this->command) {
            $this->command->info('Spray Paint: created ' . count($created) . ' product(s).');
            foreach ($skipped as $name) {
                $this->command->warn('Skipped (exists): ' . $name);
            }
        }
    }
}