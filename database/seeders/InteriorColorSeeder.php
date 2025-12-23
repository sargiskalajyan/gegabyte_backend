<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InteriorColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Սև', 'en'=>'Black', 'ru'=>'Черный'],
            ['hy'=>'Բեժ', 'en'=>'Beige', 'ru'=>'Бежевый'],
        ];

        foreach ($items as $item) {
            $id = DB::table('interior_colors')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('interior_color_translations')->insert([
                    'interior_color_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
