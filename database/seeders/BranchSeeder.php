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
        Branch::create(['branch_name' => 'Branch 1 - Moroboro', 'location' => 'Brgy Moroboro Dingle, Ilo-ilo']);
        Branch::create(['branch_name' => 'Branch 2 - Poblacion', 'location' => 'Brgy. Poblacion Muyco St. Dingle, Ilo-ilo']);
        Branch::create(['branch_name' => 'Branch 3 - San Matias', 'location' => 'Brgy. San Matias Dingle, Ilo-ilo']);
        Branch::create(['branch_name' => 'Branch 4 - Banate', 'location' => 'Bularan St. Banate, Iloilo']);
    }
}
