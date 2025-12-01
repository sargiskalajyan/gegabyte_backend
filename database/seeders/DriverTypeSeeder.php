<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverTypeSeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Աջ', 'en'=>'Right-hand drive', 'ru'=>'Правый руль'],
            ['hy'=>'Ձախ', 'en'=>'Left-hand drive', 'ru'=>'Левый руль'],
        ];

        foreach ($items as $item) {
            $id = DB::table('driver_types')->insertGetId([]);

            foreach ($languages as $langCode => $langId) {
                DB::table('driver_type_translations')->insert([
                    'driver_type_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
