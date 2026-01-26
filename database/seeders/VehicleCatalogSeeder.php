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

        // Clear tables that will be repopulated
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('category_translations')->truncate();
        DB::table('categories')->truncate();
        DB::table('make_translations')->truncate();
        DB::table('makes')->truncate();
        DB::table('car_model_translations')->truncate();
        DB::table('car_models')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

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

                'trucks' => [
                    'hy' => 'Բեռնատարներ',
                    'en' => 'Trucks',
                    'ru' => 'Грузовики',
                ],

                'motorcycles' => [
                    'hy' => 'Մոտոցիկլներ',
                    'en' => 'Motorcycles',
                    'ru' => 'Мотоциклы',
                ],

                'special_vehicles' => [
                    'hy' => 'Հատուկ տրանսպորտային միջոցներ',
                    'en' => 'Special vehicles',
                    'ru' => 'Специальные транспортные средства',
                ],

                'buses' => [
                    'hy' => 'Ավտոբուսներ',
                    'en' => 'Buses',
                    'ru' => 'Автобусы',
                ],

                'trailers' => [
                    'hy' => 'Կցասայլեր',
                    'en' => 'Trailers',
                    'ru' => 'Прицепы',
                ],

                'water_vehicles' => [
                    'hy' => 'Ջրային տրանսպորտային միջոցներ',
                    'en' => 'Water vehicles',
                    'ru' => 'Водные транспортные средства',
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

            // Map local category keys to auto.am category IDs
            $autoCategoryMap = [
                'cars' => 1, // passenger-cars
                'trucks' => 5,
                'motorcycles' => 2,
                'special_vehicles' => 6,
                'buses' => 4,
                'trailers' => 30,
                'water_vehicles' => 25,
            ];



            // For each created category, fetch makes/models from auto.am (by auto category id)
            foreach ($categoryIds as $key => $appCategoryId) {
                $autoId = $autoCategoryMap[$key] ?? null;
                if (empty($autoId)) {
                    continue;
                }

                $makesAndModels = $vehicleService->getMakesAndModelsFromAutoAm($autoId);
                if (empty($makesAndModels)) {
                    continue;
                }

                foreach ($makesAndModels as $make => $models) {
                    $makeId = DB::table('makes')->insertGetId([
                        'category_id' => $appCategoryId,
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
            }
        });
    }
}
