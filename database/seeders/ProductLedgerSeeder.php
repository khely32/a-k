<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductLedgerSeeder extends Seeder
{
    public function run()
    {
        // Data parsed directly from your ledger sheet image (Screenshot 2026-07-06 155754.jpg)
        $items = [
            ['name' => 'Wave 100', 'type' => 'Std', 'brand' => 'Vismat', 'price' => 200.00, 'quantity' => 20],
            ['name' => 'Wave 100', 'type' => 'C1', 'brand' => 'Vismat', 'price' => 203.00, 'quantity' => 15],
            ['name' => 'Wave 100', 'type' => 'C2', 'brand' => 'Vismat', 'price' => 203.00, 'quantity' => 15],
            
            ['name' => 'XR200', 'type' => 'Std (13011-KCY-671)', 'brand' => 'Solid', 'price' => 420.00, 'quantity' => 10],
            ['name' => 'XR200', 'type' => 'Std (13011-KCY-901)', 'brand' => 'Vismat', 'price' => 250.00, 'quantity' => 12],
            
            ['name' => 'Mio 125', 'type' => 'Std (2PH-E1603-10)', 'brand' => 'Solid', 'price' => 399.00, 'quantity' => 8],
            ['name' => 'Mio 125', 'type' => 'Std (2PH-E1603-10)', 'brand' => 'Vismat', 'price' => 203.00, 'quantity' => 10],
            
            ['name' => 'Discover 100', 'type' => 'Std', 'brand' => 'Solid', 'price' => 355.00, 'quantity' => 5],
            
            ['name' => 'Click 125', 'type' => 'Std Piston (13011-K60-T00)', 'brand' => 'Solid', 'price' => 257.00, 'quantity' => 6],
            ['name' => 'Click 125', 'type' => 'Ring (13011-K60-B00)', 'brand' => 'Solid', 'price' => 182.00, 'quantity' => 14],
            ['name' => 'Click 125', 'type' => 'Std', 'brand' => 'Vismat', 'price' => 160.00, 'quantity' => 10],
            
            ['name' => 'Mio Sporty', 'type' => 'Std (5MX-E1603-10)', 'brand' => 'Solid', 'price' => 458.00, 'quantity' => 7],
            
            ['name' => 'Fury', 'type' => 'Std', 'brand' => 'Proper', 'price' => 210.00, 'quantity' => 10],
            ['name' => 'Fury', 'type' => 'Std', 'brand' => 'Vismat', 'price' => 203.00, 'quantity' => 12],
            ['name' => 'Fury', 'type' => 'S1', 'brand' => 'RK', 'price' => 295.00, 'quantity' => 5],
            
            ['name' => 'G110', 'type' => 'Std (12140-36H00-000)', 'brand' => 'Solid', 'price' => 204.00, 'quantity' => 8],
            ['name' => 'Smash 115', 'type' => 'Std (12140-36H50)', 'brand' => 'Solid', 'price' => 214.00, 'quantity' => 15],
            ['name' => 'Smash 115', 'type' => 'Std (12140-36H50)', 'brand' => 'Vismat', 'price' => 196.00, 'quantity' => 10],
            
            ['name' => 'Pinoy 125', 'type' => 'Std', 'brand' => 'Proper', 'price' => 210.00, 'quantity' => 6],
            ['name' => 'Pinoy 125', 'type' => 'Std', 'brand' => 'Vismat', 'price' => 220.00, 'quantity' => 8],
            
            ['name' => 'C100', 'type' => 'Std', 'brand' => 'RK', 'price' => 220.00, 'quantity' => 20],
            ['name' => 'C100', 'type' => '0.25', 'brand' => 'Vismat', 'price' => 54.00, 'quantity' => 15],
        ];

        // Loop and create entries based on your exact Product attributes
        foreach ($items as $item) {
            Product::create([
                'name'     => $item['name'],
                'brand'    => $item['brand'],
                'type'     => $item['type'],
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
                // serial_number is ignored here since your model boot method builds it automatically!
            ]);
        }
    }
}