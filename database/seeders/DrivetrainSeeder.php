<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrivetrainSeeder extends Seeder
{
    public function run()
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Առջև', 'en'=>'FWD', 'ru'=>'Передний'],
            ['hy'=>'Հետև', 'en'=>'RWD', 'ru'=>'Задний'],
            ['hy'=>'Ամբողջական', 'en'=>'AWD / 4x4', 'ru'=>'Полный привод'],
        ];

        foreach ($items as $item) {
            $id = DB::table('drivetrains')->insertGetId([]);

            foreach ($languages as $langCode => $langId) {
                DB::table('drivetrain_translations')->insert([
                    'drivetrain_id' => $id,
                    'language_id' => $langId,
                    'name'        => $item[$langCode],
                ]);
            }
        }
    }
}
