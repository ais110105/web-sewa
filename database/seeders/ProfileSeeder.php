<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create profile for Owner
        $owner = User::where('email', 'owner@example.com')->first();
        if ($owner) {
            Profile::firstOrCreate(
                ['user_id' => $owner->id],
                [
                    'full_name' => 'John Doe',
                    'phone' => '+6281234567890',
                    'address' => 'Jl. Raya Owner No. 1, Jakarta',
                ]
            );
        }

        // Create profile for Admin
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            Profile::firstOrCreate(
                ['user_id' => $admin->id],
                [
                    'full_name' => 'Jane Smith',
                    'phone' => '+6281234567891',
                    'address' => 'Jl. Raya Admin No. 2, Jakarta',
                ]
            );
        }

        // Create profile for User
        $user = User::where('email', 'user@example.com')->first();
        if ($user) {
            Profile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => 'Bob Johnson',
                    'phone' => '+6281234567892',
                    'address' => 'Jl. Raya User No. 3, Jakarta',
                ]
            );
        }
    }
}
