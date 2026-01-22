<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AdminSeeder::class,
            LanguageSeeder::class,
            LocationSeeder::class,
            VehicleCatalogSeeder::class,
            FuelSeeder::class,
            TransmissionSeeder::class,
            DrivetrainSeeder::class,
            ConditionSeeder::class,
            ColorSeeder::class,
            CurrencySeeder::class,
            DriverTypeSeeder::class,
            EngineSizeSeeder::class,
            PackageSeeder::class,
            EngineSeeder::class,

            GasEquipmentSeeder::class,
            WheelSizeSeeder::class,
            HeadlightsSeeder::class,
            InteriorColorSeeder::class,
            InteriorMaterialSeeder::class,
            SteeringWheelSeeder::class,
        ]);



//        php artisan db:seed --class=VehicleCatalogSeeder
    }
}
