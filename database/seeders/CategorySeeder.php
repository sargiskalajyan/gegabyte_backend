<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Load languages: ['hy' => 1, 'en' => 2, 'ru' => 3]
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy' => 'Մեքենաներ',      'en' => 'Cars',         'ru' => 'Авто'],
            ['hy' => 'Մոտոցիկլներ',   'en' => 'Motorcycles',  'ru' => 'Мотоциклы'],
            ['hy' => 'Բեռնատարներ',   'en' => 'Trucks',       'ru' => 'Грузовики'],
        ];

        foreach ($items as $item) {
            $categoryId = DB::table('categories')->insertGetId([]);
            foreach ($languages as $langCode => $langId) {
                DB::table('category_translations')->insert([
                    'category_id'  => $categoryId,
                    'language_id'  => $langId,
                    'name'         => $item[$langCode],
                ]);
            }
        }
    }
}
