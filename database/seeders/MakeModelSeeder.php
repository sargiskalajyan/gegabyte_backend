<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MakeModelSeeder extends Seeder
{
    public function run()
    {
        // Load language codes => IDs from the languages table
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $data = [
            "BMW" => ["M3", "M4", "M5", "X5", "X6"],
            "Mercedes-Benz" => ["C200", "E220", "GLE 350", "S550"],
            "Acura" => ["ILX", "Integra", "MDX", "RDX"],
        ];

        foreach ($data as $make => $models) {

            // Insert Make
            $makeId = DB::table('makes')->insertGetId([]);

            // Insert Make Translations
            foreach ($languages as $code => $langId) {
                DB::table('make_translations')->insert([
                    'make_id'      => $makeId,
                    'language_id'  => $langId,
                    'name'         => $make,
                ]);
            }

            // Insert Models
            foreach ($models as $modelName) {

                $modelId = DB::table('car_models')->insertGetId([
                    'make_id'    => $makeId,
                ]);

                // Insert Model Translations
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
}
