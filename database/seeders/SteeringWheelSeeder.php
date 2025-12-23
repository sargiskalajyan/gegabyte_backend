<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SteeringWheelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Սովորական', 'en'=>'Standard', 'ru'=>'Standard'],
            ['hy'=>'Սպորտային', 'en'=>'Sport', 'ru'=>'Sport'],
        ];

        foreach ($items as $item) {
            $id = DB::table('steering_wheels')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('steering_wheel_translations')->insert([
                    'steering_wheel_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
