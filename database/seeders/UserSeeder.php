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

        // Admin (full control)
        User::firstOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Owner Admin',
                'password' => Hash::make('admin123456789'),
                'role' => 'owner',
                'branch_id' => $mainBranch ? $mainBranch->id : null,
            ]
        );

        // One staff user per branch
        $branches = \App\Models\Branch::orderBy('id')->get();
        foreach ($branches as $branch) {
            $userName = $branch->branch_name . ' staff';
            $slug = strtolower(str_replace(' ', '', preg_replace('/\s+Branch$/i', '', $branch->branch_name)));
            User::firstOrCreate(
                ['email' => "{$slug}@akmotorcycle.com"],
                [
                    'name' => $userName,
                    'password' => Hash::make('password'),
                    'role' => 'staff',
                    'branch_id' => $branch->id,
                ]
            );
        }
    }
}
