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
            ['branch_name' => 'Moroboro Branch', 'location' => 'Brgy Moroboro Dingle, Ilo-ilo'],
            ['branch_name' => 'Poblacion Branch', 'location' => 'Brgy. Poblacion Muyco St. Dingle, Ilo-ilo'],
            ['branch_name' => 'San Matias Branch', 'location' => 'Brgy. San Matias Dingle, Ilo-ilo'],
            ['branch_name' => 'Banate Branch', 'location' => 'Bularan St. Banate, Iloilo'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(
                ['branch_name' => $branch['branch_name']],
                ['location' => $branch['location']]
            );
        }
    }
}
