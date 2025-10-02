<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create([
            'first_name' => 'Omar',
            'last_name' => 'Faruk',
            'email' => 'omar@yopmail.com',
            'password' => '11111111',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        //assing role to user


        $this->call([
            RolesAndPermissionsSeeder::class,
            StateSeeder::class,
            // Add other seeders here as needed
        ]);

        $user->assignRole('admin');
    }
}
