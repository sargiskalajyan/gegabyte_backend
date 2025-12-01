<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransmissionSeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy' => 'Մեխանիկական', 'en' => 'Manual', 'ru' => 'Механика'],
            ['hy' => 'Ավտոմատ', 'en' => 'Automatic', 'ru' => 'Автомат'],
            ['hy' => 'Կեսավտոմատ', 'en' => 'Semi-Automatic', 'ru' => 'Полуавтомат'],
            ['hy' => 'Վարիատոր', 'en' => 'CVT', 'ru' => 'Вариатор'],
        ];


        foreach ($items as $item) {
            $id = DB::table('transmissions')->insertGetId([]);

            foreach ($languages as $langCode => $langId) {
                DB::table('transmission_translations')->insert([
                    'transmission_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
