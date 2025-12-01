<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    public function run()
    {
        // Load languages: ['hy' => 1, 'en' => 2, 'ru' => 3]
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['code' => '#FFFFFF', 'hy'=>'Սպիտակ',     'en'=>'White',  'ru'=>'Белый'],
            ['code' => '#000000', 'hy'=>'Սև',         'en'=>'Black',  'ru'=>'Черный'],
            ['code' => '#FF0000', 'hy'=>'Կարմիր',     'en'=>'Red',    'ru'=>'Красный'],
            ['code' => '#0000FF', 'hy'=>'Կապույտ',    'en'=>'Blue',   'ru'=>'Синий'],
            ['code' => '#808080', 'hy'=>'Մոխրագույն', 'en'=>'Gray',   'ru'=>'Серый'],
            ['code' => '#FFFF00', 'hy'=>'Դեղին',      'en'=>'Yellow', 'ru'=>'Жёлтый'],
        ];

        foreach ($items as $item) {
            $id = DB::table('colors')->insertGetId([
                'code'       => $item['code'],
            ]);
            foreach ($languages as $langCode => $langId) {
                DB::table('color_translations')->insert([
                    'color_id'    => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
