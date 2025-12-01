<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EngineSeeder extends Seeder
{
    public function run()
    {
        // Get language codes => IDs dynamically from the languages table
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        // Engine types with translations
        $engines = [
            ['hy' => 'Բենզին', 'en' => 'Petrol', 'ru' => 'Бензин'],
            ['hy' => 'Դիզել', 'en' => 'Diesel', 'ru' => 'Дизель'],
            ['hy' => 'Հիբրիդ', 'en' => 'Hybrid', 'ru' => 'Гибрид'],
            ['hy' => 'Էլեկտրիկ', 'en' => 'Electric', 'ru' => 'Электро'],
        ];

        foreach ($engines as $engine) {

            // Insert engine (without translation)
            $engineId = DB::table('engines')->insertGetId([]);

            // Insert translations for each language
            foreach ($languages as $langCode => $langId) {
                DB::table('engine_translations')->insert([
                    'engine_id'  => $engineId,
                    'language_id'=> $langId,
                    'name'       => $engine[$langCode],
                ]);
            }
        }
    }
}
