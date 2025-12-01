<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionSeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Օգտագործված', 'en'=>'Used', 'ru'=>'Б/У'],
            ['hy'=>'Նոր', 'en'=>'New', 'ru'=>'Новый'],
            ['hy'=>'Վթարված', 'en'=>'Damaged', 'ru'=>'Повреждён'],
        ];

        foreach ($items as $item) {
            $id = DB::table('conditions')->insertGetId([]);

            foreach ($languages as $langCode => $langId) {
                DB::table('condition_translations')->insert([
                    'condition_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
