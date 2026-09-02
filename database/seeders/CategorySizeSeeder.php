<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategorySize;

class CategorySizeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Engine Oil'           => ['1L', '4L'],
            'Brake Pad'            => ['Standard', 'Premium'],
            'Tire'                 => ['17 inch', '18 inch', '19 inch', '21 inch'],
            'Chain'                => ['428', '520', '525', '530'],
            'Spark Plug'           => ['Standard', 'Iridium'],
            'Battery'              => ['12V', '12V MF'],
            'Oil Filter'           => ['Standard', 'Premium'],
            'Air Filter'           => ['Standard', 'High Flow'],
            'Brake Fluid'          => ['DOT 3', 'DOT 4', 'DOT 5.1'],
            'Coolant'              => ['1L', '4L'],
            'Light Bulb'           => ['H4', 'H7', 'LED'],
            'Gasket Set'           => ['Standard', 'Premium'],
            'Bearing'              => ['Standard', 'Sealed'],
            'Cable'                => ['Standard', 'Racing'],
            'Gear Oil'             => ['80W-90', '85W-140'],
            'Fork Oil'             => ['10W', '15W', '20W'],
            'Clutch Plate'         => ['Standard', 'Heavy Duty'],
            'Disc Brake Rotor'     => ['Front', 'Rear'],
            'Rim'                  => ['17 inch', '18 inch', '21 inch'],
            'Handle Grip'          => ['Standard', 'Foam'],
            'Mirror'               => ['Left', 'Right', 'Pair'],
            'Tail Light'           => ['LED', 'Bulb'],
            'Headlight'            => ['LED', 'Halogen'],
            'Horn'                 => ['Standard', 'Magnum'],
            'Fuel Filter'          => ['Standard', 'Premium'],
            'Starter Relay'        => ['Standard', 'Heavy Duty'],
            'Regulator Rectifier'  => ['Standard', 'Upgraded'],
            'Ignition Coil'        => ['Standard', 'High Output'],
            'CDI Unit'             => ['Standard', 'Racing'],
            'Tire Tube'            => ['17 inch', '18 inch', '19 inch', '21 inch'],
            'Accessories'          => ['Universal', 'Small', 'Medium', 'Large'],
            'Lubricants'           => ['Spray', 'Grease', 'Oil'],
            'Tools'                => ['Set', 'Piece'],
        ];

        $order = 0;
        foreach ($data as $category => $sizes) {
            foreach ($sizes as $size) {
                CategorySize::create([
                    'category'   => $category,
                    'size'       => $size,
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
