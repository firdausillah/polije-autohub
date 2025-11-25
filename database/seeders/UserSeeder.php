<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Buat role kalau belum ada
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mekanik', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kepala_mekanik', 'guard_name' => 'web']);

        // Buat user default
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // Ganti dengan password yang aman
            ]
        );
        $adminUser->assignRole('super_admin');

        $mekanikUser = User::firstOrCreate(
            ['email' => 'mekanik@mekanik.com'],
            [
                'name' => 'Mekanik User',
                'password' => bcrypt('password'),
            ]
        );
        $mekanikUser->assignRole('mekanik');

        $kepalaUnitUser = User::firstOrCreate(
            ['email' => 'kepala_mekanik@kepala_mekanik.com'],
            [
                'name' => 'Kepala Unit User',
                'password' => bcrypt('password'),
            ]
        );
        $kepalaUnitUser->assignRole('kepala_mekanik');

        echo "User default berhasil ditambahkan beserta role-nya.\n";
    }
}
