<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['code'=>'AMD', 'hy'=>'Դրամ', 'en'=>'Armenian Dram', 'ru'=>'Драм'],
            ['code'=>'USD', 'hy'=>'Դոլար', 'en'=>'US Dollar', 'ru'=>'Доллар'],
            ['code'=>'EUR', 'hy'=>'Եվրո', 'en'=>'Euro', 'ru'=>'Евро'],
        ];

        foreach ($items as $item) {
            $id = DB::table('currencies')->insertGetId([
                'code'=>$item['code']
            ]);
            foreach ($languages as $langCode => $langId) {
                DB::table('currency_translations')->insert([
                    'currency_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
