<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Kepala Lab
        User::factory()->create([
            'name' => 'Kepala Laboratorium',
            'email' => 'kepala_lab@example.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_lab',
        ]);

        // Kaprodi
        User::factory()->create([
            'name' => 'Ketua Program Studi',
            'email' => 'kaprodi@example.com',
            'password' => bcrypt('password'),
            'role' => 'kaprodi',
        ]);

        // Staff Admin
        User::factory()->create([
            'name' => 'Staff Administrasi',
            'email' => 'staff_admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff_admin',
        ]);

        // Staff Lab
        User::factory()->create([
            'name' => 'Staff Laboratorium',
            'email' => 'staff_lab@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff_lab',
        ]);
    }
}
