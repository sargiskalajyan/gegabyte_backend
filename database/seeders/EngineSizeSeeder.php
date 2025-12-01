<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EngineSizeSeeder extends Seeder
{
    public function run()
    {
        // Get language codes => IDs dynamically from the languages table
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $sizes = [
            "1.0", "1.2", "1.3", "1.4", "1.5", "1.6",
            "1.8", "2.0", "2.2", "2.4", "2.5", "2.7",
            "3.0", "3.2", "3.5", "3.7", "4.0", "4.4",
            "5.0", "5.5", "6.0", "6.2"
        ];

        foreach ($sizes as $s) {

            // Insert engine size
            $engineId = DB::table('engine_sizes')->insertGetId([
                'value'      => $s,
            ]);

            // Insert translations for each language
            foreach ($languages as $langCode => $langId) {
                DB::table('engine_size_translations')->insert([
                    'engine_size_id' => $engineId,
                    'language_id'    => $langId,
                    'name'           => $s,
                ]);
            }
        }
    }
}
