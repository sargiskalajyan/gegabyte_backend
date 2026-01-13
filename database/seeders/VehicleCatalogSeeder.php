<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\VehicleCatalogService;

class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleService = new VehicleCatalogService();

        DB::transaction(function () use ($vehicleService) {
            // Languages: code => id
            $languages = DB::table('languages')->pluck('id', 'code')->toArray();

            // Static Categories
            $categories = [
                'cars' => [
                    'hy' => 'Մեքենաներ',
                    'en' => 'Cars',
                    'ru' => 'Авто',
                ],
                'motorcycles' => [
                    'hy' => 'Մոտոցիկլներ',
                    'en' => 'Motorcycles',
                    'ru' => 'Мотоциклы',
                ],
                'trucks' => [
                    'hy' => 'Բեռնատարներ',
                    'en' => 'Trucks',
                    'ru' => 'Грузовики',
                ],
            ];

            $categoryIds = [];
            foreach ($categories as $key => $translations) {
                $categoryId = DB::table('categories')->insertGetId([]);

                foreach ($languages as $code => $langId) {
                    DB::table('category_translations')->insert([
                        'category_id' => $categoryId,
                        'language_id' => $langId,
                        'name'        => $translations[$code],
                    ]);
                }

                $categoryIds[$key] = $categoryId;
            }

            // Dynamically Fetch Makes and Models for Cars
            $makesAndModels = $vehicleService->getMakesAndModels();

            foreach ($makesAndModels as $make => $models) {
                $makeId = DB::table('makes')->insertGetId([
                    'category_id' => $categoryIds['cars'],
                ]);

                foreach ($languages as $code => $langId) {
                    DB::table('make_translations')->insert([
                        'make_id'     => $makeId,
                        'language_id' => $langId,
                        'name'        => $make,
                    ]);
                }

                foreach ($models as $modelName) {
                    $modelId = DB::table('car_models')->insertGetId([
                        'make_id' => $makeId,
                    ]);

                    foreach ($languages as $code => $langId) {
                        DB::table('car_model_translations')->insert([
                            'car_model_id' => $modelId,
                            'language_id'  => $langId,
                            'name'         => $modelName,
                        ]);
                    }
                }
            }
        });
    }
}