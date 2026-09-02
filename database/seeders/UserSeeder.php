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
        // Admin
        User::create([
            'name' => 'Owner Admin',
            'username' => 'admin',
            'email' => 'admin@akmotorcycle.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'branch_id' => null,
        ]);

        // Branch Managers/Cashiers for testing
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'name' => "Manager Branch $i",
                'username' => "manager$i",
                'email' => "manager$i@akmotorcycle.com",
                'password' => Hash::make('password'),
                'role' => 'manager',
                'branch_id' => $i,
            ]);

            User::create([
                'name' => "Cashier Branch $i",
                'username' => "cashier$i",
                'email' => "cashier$i@akmotorcycle.com",
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'branch_id' => $i,
            ]);
        }
    }
}
