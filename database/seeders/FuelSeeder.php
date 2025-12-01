<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FuelSeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy' => 'Բենզին', 'en' => 'Petrol', 'ru' => 'Бензин'],
            ['hy' => 'Դիզել', 'en' => 'Diesel', 'ru' => 'Дизель'],
            ['hy' => 'Գազ/Բենզին', 'en' => 'Gas/Petrol', 'ru' => 'Газ/Бензин'],
            ['hy' => 'Էլեկտրիկ', 'en' => 'Electric', 'ru' => 'Электро'],
        ];

        foreach ($items as $item) {

            $id = DB::table('fuels')->insertGetId([]);

            foreach ($languages as $langCode => $langId) {
                DB::table('fuel_translations')->insert([
                    'fuel_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
