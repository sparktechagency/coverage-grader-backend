<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset the cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Admin Role
        Role::create(['name' => 'admin']);

        // 2. User Role
        Role::create(['name' => 'user']);
    }
}

// php artisan db:seed --class=RolesAndPermissionsSeeder
