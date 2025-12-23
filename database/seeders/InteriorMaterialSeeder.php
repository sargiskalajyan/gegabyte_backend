<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InteriorMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $items = [
            ['hy'=>'Կաշի', 'en'=>'Leather', 'ru'=>'Кожа'],
            ['hy'=>'Կտոր', 'en'=>'Fabric', 'ru'=>'Ткань'],
        ];

        foreach ($items as $item) {
            $id = DB::table('interior_materials')->insertGetId([]);

            foreach ($languages as $code => $langId) {
                DB::table('interior_material_translations')->insert([
                    'interior_material_id' => $id,
                    'language_id' => $langId,
                    'name' => $item[$code],
                ]);
            }
        }
    }
}
