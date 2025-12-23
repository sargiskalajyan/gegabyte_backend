<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadlightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Հալոգեն', 'en'=>'Halogen', 'ru'=>'Галоген'],
            ['hy'=>'Քսենոն', 'en'=>'Xenon', 'ru'=>'Ксенон'],
            ['hy'=>'Լեդ', 'en'=>'LED', 'ru'=>'LED'],
        ];

        foreach ($items as $item) {
            $id = DB::table('headlights')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('headlight_translations')->insert([
                    'headlight_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
