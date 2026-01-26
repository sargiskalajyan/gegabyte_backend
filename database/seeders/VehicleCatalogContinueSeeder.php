<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\VehicleCatalogService;

class VehicleCatalogContinueSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleService = new VehicleCatalogService();

        DB::transaction(function () use ($vehicleService) {
            $languages = DB::table('languages')->pluck('id', 'code')->toArray();

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

            $autoCategoryMap = [
                'cars' => 1,
                'trucks' => 5,
                'motorcycles' => 2,
                'special_vehicles' => 6,
                'buses' => 4,
                'trailers' => 30,
                'water_vehicles' => 25,
            ];

            foreach ($categories as $key => $translations) {
                $langEnId = $languages['en'] ?? null;
                $categoryNameEn = $translations['en'];

                $categoryId = null;
                if ($langEnId) {
                    $categoryId = DB::table('category_translations')
                        ->where('language_id', $langEnId)
                        ->where('name', $categoryNameEn)
                        ->value('category_id');
                }

                if (!$categoryId) {
                    // create category and translations
                    $categoryId = DB::table('categories')->insertGetId([]);
                    foreach ($languages as $code => $langId) {
                        DB::table('category_translations')->insert([
                            'category_id' => $categoryId,
                            'language_id' => $langId,
                            'name' => $translations[$code] ?? $categoryNameEn,
                        ]);
                    }
                }

                // If this category already has makes, skip fetching/inserting makes/models
                $hasMakes = DB::table('makes')->where('category_id', $categoryId)->exists();
                if ($hasMakes) {
                    continue;
                }

                $autoId = $autoCategoryMap[$key] ?? null;
                if (empty($autoId)) {
                    continue;
                }

                $makesAndModels = $vehicleService->getMakesAndModelsFromAutoAm($autoId);
                if (empty($makesAndModels)) {
                    continue;
                }

                foreach ($makesAndModels as $makeName => $models) {
                    // Try find existing make by English translation
                    $existingMakeId = null;
                    if ($langEnId) {
                        $candidate = DB::table('make_translations')
                            ->where('language_id', $langEnId)
                            ->where('name', $makeName)
                            ->value('make_id');

                        if ($candidate && DB::table('makes')->where('id', $candidate)->where('category_id', $categoryId)->exists()) {
                            $existingMakeId = $candidate;
                        }
                    }

                    if ($existingMakeId) {
                        $makeId = $existingMakeId;
                    } else {
                        $makeId = DB::table('makes')->insertGetId([
                            'category_id' => $categoryId,
                        ]);

                        foreach ($languages as $code => $langId) {
                            DB::table('make_translations')->insert([
                                'make_id' => $makeId,
                                'language_id' => $langId,
                                'name' => $makeName,
                            ]);
                        }
                    }

                    foreach ($models as $modelName) {
                        // check if model exists for this make
                        $exists = DB::table('car_model_translations as cmt')
                            ->join('car_models as cm', 'cmt.car_model_id', '=', 'cm.id')
                            ->where('cmt.language_id', $langEnId)
                            ->where('cmt.name', $modelName)
                            ->where('cm.make_id', $makeId)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        $modelId = DB::table('car_models')->insertGetId([
                            'make_id' => $makeId,
                        ]);

                        foreach ($languages as $code => $langId) {
                            DB::table('car_model_translations')->insert([
                                'car_model_id' => $modelId,
                                'language_id' => $langId,
                                'name' => $modelName,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
