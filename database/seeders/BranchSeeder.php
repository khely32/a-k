<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            ['branch_name' => 'Moroboro Branch', 'location' => 'Brgy Moroboro Dingle, Ilo-ilo', 'is_main' => true],
            ['branch_name' => 'Poblacion Branch', 'location' => 'Brgy. Poblacion Muyco St. Dingle, Ilo-ilo', 'is_main' => false],
            ['branch_name' => 'San Matias Branch', 'location' => 'Brgy. San Matias Dingle, Ilo-ilo', 'is_main' => false],
            ['branch_name' => 'Banate Branch', 'location' => 'Buran St. Banate, Iloilo', 'is_main' => false],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['branch_name' => $branch['branch_name']],
                ['location' => $branch['location'], 'is_main' => $branch['is_main']]
            );
        }
    }
}
