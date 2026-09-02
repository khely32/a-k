<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = \App\Models\Branch::whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])->first();
        $branchId = $mainBranch ? $mainBranch->id : null;

        $products = [
            ['serial_number' => 'HON-OIL-001', 'name' => '4T Engine Oil', 'brand' => 'Honda', 'type' => 'Lubricant', 'quantity' => 50, 'price' => 320.00],
            ['serial_number' => 'YAM-SPK-001', 'name' => 'Spark Plug', 'brand' => 'Yamaha', 'type' => 'Engine Part', 'quantity' => 40, 'price' => 180.00],
            ['serial_number' => 'MOT-BAT-001', 'name' => 'Motorcycle Battery', 'brand' => 'Motolite', 'type' => 'Electrical', 'quantity' => 20, 'price' => 1200.00],
            ['serial_number' => 'MIC-TIR-001', 'name' => 'Motorcycle Tire', 'brand' => 'Michelin', 'type' => 'Tire', 'quantity' => 15, 'price' => 1850.00],
            ['serial_number' => 'BRE-BRK-001', 'name' => 'Brake Pad', 'brand' => 'Brembo', 'type' => 'Brake System', 'quantity' => 25, 'price' => 850.00],
            ['serial_number' => 'NGK-SPK-001', 'name' => 'Iridium Spark Plug', 'brand' => 'NGK', 'type' => 'Engine Part', 'quantity' => 30, 'price' => 450.00],
        ];

        foreach ($products as $item) {
            $product = Product::firstOrCreate(
                ['serial_number' => $item['serial_number']],
                [
                    'name' => $item['name'],
                    'brand' => $item['brand'],
                    'type' => $item['type'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'branch_id' => $branchId,
                ]
            );

            if ($branchId) {
                Inventory::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $branchId],
                    ['quantity' => $item['quantity']]
                );
            }
        }
    }
}