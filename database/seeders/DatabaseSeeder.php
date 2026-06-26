<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FuelLog;
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
        // Seeding user - aman dijalankan berkali-kali, gak akan duplikat
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Seeding fuel logs - pakai firstOrCreate, gak akan duplikat
        FuelLog::firstOrCreate(
            ['vehicle_id' => 1, 'filled_at' => '2026-05-16 10:00:00'],
            [
                'driver_name' => 'Adwitiya Tikta Pramasti',
                'liters' => 40.5,
                'total_cost' => 350000.0,
                'fuel_station' => 'SPBU Pertamina Bandung',
                'soap_receipt_number' => 'REC-123456',
            ]
        );

        FuelLog::firstOrCreate(
            ['vehicle_id' => 2, 'filled_at' => '2026-06-20 15:30:00'],
            [
                'driver_name' => 'Calista Aurelia Putri',
                'liters' => 50.0,
                'total_cost' => 450000.0,
                'fuel_station' => 'SPBU Pertamina Jakarta',
                'soap_receipt_number' => null,
            ]
        );
    }
}