<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WheelSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'R15', 'en'=>'R15', 'ru'=>'R15'],
            ['hy'=>'R16', 'en'=>'R16', 'ru'=>'R16'],
            ['hy'=>'R17', 'en'=>'R17', 'ru'=>'R17'],
        ];

        foreach ($items as $item) {
            $id = DB::table('wheel_sizes')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('wheel_size_translations')->insert([
                    'wheel_size_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
