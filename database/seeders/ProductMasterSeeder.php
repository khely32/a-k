<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductMasterSeeder extends Seeder
{
    public function run(): void
    {
        $productNames = [
            // Wave 100 series
            'WAVE100 STD Base',
            'WAVE100 C1',
            'WAVE100 C2',
            'WAVE100 C4',
            
            // XR200 series
            'XR200 STD (13011-KPC-901)',
            'XR200 C1',
            'XR200 C4',
            
            // Mio 125 series
            'MIO 125 STD Base',
            'MIO 125 (2PH-E1603-10)',
            
            // Discover
            'DISCOVER 100 STD',
            
            // Click 125 series
            'CLICK 125 STD (Piston)',
            'CLICK 125 Ring (13011-KWN-900)',
            'CLICK 125 Ring (13011-K35-V00)',
            
            // Click 150 series
            'CLICK 150 STD (Ring)',
            'CLICK 150 Ring (13011-K93-N00)',
            
            // YTX series
            'YTX STD (2UP-E1603-000)',
            'YTX 0.50 (2UP-E1605-00)',
            
            // XRM series
            'XRM STD',
            
            // Mio Sporty
            'MIO SPORTY STD (5MY-E1603-10)',
            
            // Fury series
            'FURY STD',
            'FURY C1',
            'FURY C2',
            
            // Mio i125 / M3
            'MIO i125 / MIO M3 STD',
            
            // GD110
            'GD110 STD (12140-36H00-000)',
            
            // Smash 115
            'SMASH 115 STD (12140-26H00)',
            
            // Platina 125
            'Platina 125 STD',
            
            // C100 series
            'C100 STD',
            'C100 0.25',
        ];

        foreach ($productNames as $name) {
            Product::firstOrCreate(
                ['name' => $name], // Check if name exists
                [
                    'price' => 0.00, // Safe default price constraint fulfiller
                    'serial_number' => 'AK-' . strtoupper(Str::random(6)), // Generates a clean sequential-style key
                ]
            );
        }
    }
}