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
        $branches = \App\Models\Branch::orderBy('id')->get();

        // One user per branch; the Moroboro branch account is the owner/admin
        foreach ($branches as $branch) {
            $isMain = str_contains(strtolower($branch->branch_name), 'moroboro');
            $slug = strtolower(str_replace(' ', '', preg_replace('/\s+Branch$/i', '', $branch->branch_name)));
            $email = $isMain ? 'admin' : "{$slug}@akmotorcycle.com";
            $plainPassword = $isMain ? 'admin123456789' : 'password';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $branch->branch_name,
                    'password' => Hash::make($plainPassword),
                    'plain_password' => $plainPassword,
                    'role' => $isMain ? 'owner' : 'staff',
                    'branch_id' => $branch->id,
                ]
            );

            // Always refresh name/role/plain_password so re-seeding fixes existing records
            $user->update([
                'name' => $branch->branch_name,
                'role' => $isMain ? 'owner' : 'staff',
                'branch_id' => $branch->id,
                'plain_password' => $plainPassword,
            ]);

            // Remove leftover accounts on this branch (e.g. old "X Branch staff")
            User::where('branch_id', $branch->id)
                ->where('email', '!=', $email)
                ->delete();
        }

        // Guarantee a single owner account only
        User::where('role', 'owner')
            ->where('email', '!=', 'admin')
            ->each(function ($extraOwner) {
                $extraOwner->delete();
            });
    }
}