<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('languages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('languages')->insert([
            ['name' => 'English',  'code' => 'en'],
            ['name' => 'Armenian', 'code' => 'hy'],
            ['name' => 'Russian',  'code' => 'ru'],
        ]);
    }
}
