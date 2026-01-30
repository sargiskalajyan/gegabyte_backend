<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertisementSeeder extends Seeder
{
    public function run()
    {
        // Load language ids by code
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $list = [
            [
                'key' => 'top_3_days',
                'price' => 2900,
                'duration_days' => 3,
                'names' => [
                    'hy' => 'Թոփ 3 օր',
                    'en' => 'Top 3 days',
                    'ru' => 'Топ 3 дня',
                ],
            ],
            [
                'key' => 'top_14_days',
                'price' => 7900,
                'duration_days' => 14,
                'names' => [
                    'hy' => 'Թոփ 14 օր',
                    'en' => 'Top 14 days',
                    'ru' => 'Топ 14 дней',
                ],
            ],
        ];

        foreach ($list as $item) {
            DB::table('advertisements')->updateOrInsert(
                ['key' => $item['key']],
                ['price' => $item['price'], 'duration_days' => $item['duration_days'], 'is_active' => true]
            );

            $adId = DB::table('advertisements')->where('key', $item['key'])->value('id');

            foreach ($languages as $code => $langId) {
                if (! isset($item['names'][$code])) continue;

                DB::table('advertisement_translations')->updateOrInsert(
                    [
                        'advertisement_id' => $adId,
                        'language_id' => $langId,
                    ],
                    [
                        'name' => $item['names'][$code],
                    ]
                );
            }
        }
    }
}
