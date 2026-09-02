<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = \App\Models\Branch::whereRaw('LOWER(branch_name) LIKE ?', ['%moroboro%'])->first();

        // Owner
        User::firstOrCreate(
            ['email' => 'admin@akmotorcycle.com'],
            [
                'name' => 'Owner Admin',
                'password' => Hash::make('admin123'),
                'role' => 'owner',
                'branch_id' => $mainBranch ? $mainBranch->id : null,
            ]
        );

        // Branch Managers/Cashiers for testing
        $branches = \App\Models\Branch::orderBy('id')->get();
        $i = 1;
        foreach ($branches as $branch) {
            User::firstOrCreate(
                ['email' => "manager$i@akmotorcycle.com"],
                [
                    'name' => "Manager Branch $i",
                    'password' => Hash::make('password'),
                    'role' => 'manager',
                    'branch_id' => $branch->id,
                ]
            );

            User::firstOrCreate(
                ['email' => "cashier$i@akmotorcycle.com"],
                [
                    'name' => "Cashier Branch $i",
                    'password' => Hash::make('password'),
                    'role' => 'cashier',
                    'branch_id' => $branch->id,
                ]
            );
            $i++;
        }
    }
}
