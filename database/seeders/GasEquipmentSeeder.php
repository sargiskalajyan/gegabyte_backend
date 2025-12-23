<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GasEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Մեթան', 'en'=>'Methane', 'ru'=>'Метан'],
            ['hy'=>'Պրոպան', 'en'=>'Propane', 'ru'=>'Пропан'],
        ];

        foreach ($items as $item) {
            $id = DB::table('gas_equipments')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('gas_equipment_translations')->insert([
                    'gas_equipment_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
