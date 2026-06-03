<?php

namespace Database\Seeders;

use App\Models\BhpItem;
use App\Models\InventoryItem;
use App\Models\Room;
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

        // --- Sample Rooms ---
        $labKomputer = Room::create([
            'name' => 'Lab Komputer Dasar',
            'code' => 'LAB-KOM-01',
            'location' => 'Gedung A Lantai 2',
            'capacity' => 40,
        ]);

        $labJaringan = Room::create([
            'name' => 'Lab Jaringan',
            'code' => 'LAB-JAR-01',
            'location' => 'Gedung A Lantai 3',
            'capacity' => 30,
        ]);

        $labElektronika = Room::create([
            'name' => 'Lab Elektronika',
            'code' => 'LAB-ELK-01',
            'location' => 'Gedung B Lantai 1',
            'capacity' => 25,
        ]);

        // --- Sample Inventory Items ---
        InventoryItem::create([
            'label_code' => 'INV-KOM-001',
            'name' => 'PC Desktop Rakitan',
            'condition' => 'baik',
            'room_id' => $labKomputer->id,
            'price' => 8500000,
            'purchase_date' => '2024-03-15',
            'is_labeled' => true,
        ]);

        InventoryItem::create([
            'label_code' => 'INV-KOM-002',
            'name' => 'Monitor 24" IPS Dell',
            'condition' => 'baik',
            'room_id' => $labKomputer->id,
            'price' => 2500000,
            'purchase_date' => '2024-03-15',
            'is_labeled' => true,
        ]);

        InventoryItem::create([
            'label_code' => 'INV-KOM-003',
            'name' => 'Keyboard Mechanical',
            'condition' => 'kurang_baik',
            'room_id' => $labKomputer->id,
            'price' => 350000,
            'purchase_date' => '2023-07-10',
            'is_labeled' => true,
        ]);

        InventoryItem::create([
            'label_code' => 'INV-JAR-001',
            'name' => 'Switch Managed 24-Port',
            'condition' => 'baik',
            'room_id' => $labJaringan->id,
            'price' => 4200000,
            'purchase_date' => '2024-01-20',
            'is_labeled' => true,
        ]);

        InventoryItem::create([
            'label_code' => 'INV-JAR-002',
            'name' => 'Router Cisco 2900',
            'condition' => 'rusak_berat',
            'room_id' => $labJaringan->id,
            'price' => 15000000,
            'purchase_date' => '2021-06-01',
            'is_labeled' => true,
        ]);

        InventoryItem::create([
            'label_code' => 'INV-ELK-001',
            'name' => 'Oscilloscope Digital',
            'condition' => 'baik',
            'room_id' => $labElektronika->id,
            'price' => 12000000,
            'purchase_date' => '2025-01-10',
            'is_labeled' => true,
        ]);

        // --- Sample BHP Items ---
        BhpItem::create([
            'name' => 'Alkohol 70%',
            'stock' => 3,
            'unit' => 'Liter',
            'min_stock' => 2,
            'room_id' => $labElektronika->id,
        ]);

        BhpItem::create([
            'name' => 'Kertas HVS A4',
            'stock' => 12,
            'unit' => 'Rim',
            'min_stock' => 5,
            'room_id' => null,
        ]);

        BhpItem::create([
            'name' => 'Thermal Paste',
            'stock' => 5,
            'unit' => 'Tube',
            'min_stock' => 3,
            'room_id' => $labKomputer->id,
        ]);

        BhpItem::create([
            'name' => 'Cairan Pembersih Kontak',
            'stock' => 2,
            'unit' => 'Botol',
            'min_stock' => 2,
            'room_id' => $labElektronika->id,
        ]);

        BhpItem::create([
            'name' => 'Kabel UTP Cat6',
            'stock' => 1,
            'unit' => 'Roll (100m)',
            'min_stock' => 2,
            'room_id' => $labJaringan->id,
        ]);
    }
}
