<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run()
    {
        // Load language codes => IDs dynamically
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();

        $list = [
            [
                'key' => 'free',   // added: unique stable key to prevent duplicates
                'price' => 0,
                'duration_days' => null,
                'max_active_listings' => 3,
                'included_featured_days' => 0,
                'top_listings_count' => 0,
                'names' => [
                    'hy' => 'Անվճար փաթեթ',
                    'en' => 'Free Package',
                    'ru' => 'Бесплатный пакет',
                ]
            ],
            [
                'key' => 'standard',
                'price' => 6900,
                'duration_days' => 30,
                'max_active_listings' => 6,
                'included_featured_days' => 7,
                'top_listings_count' => 1,
                'names' => [
                    'hy' => 'Սթանդարտ փաթեթ',
                    'en' => 'Standard Package',
                    'ru' => 'Стандарт',
                ]
            ],
            [
                'key' => 'premium',
                'price' => 14900,
                'duration_days' => 30,
                'max_active_listings' => 25,
                'included_featured_days' => 7,
                'top_listings_count' => 3,
                'names' => [
                    'hy' => 'Պրեմիում փաթեթ',
                    'en' => 'Premium Package',
                    'ru' => 'Премиум',
                ]
            ],
        ];

        foreach ($list as $item) {

            // Insert or update package (prevents duplicates)
            DB::table('packages')->updateOrInsert(
                ['key' => $item['key']], // unique key column must exist in migration
                [
                    'price' => $item['price'],
                    'duration_days' => $item['duration_days'],
                    'max_active_listings' => $item['max_active_listings'],
                    'included_featured_days' => $item['included_featured_days'],
                    'top_listings_count' => $item['top_listings_count'],
                ]
            );

            // get the package ID just created or existing
            $packageId = DB::table('packages')->where('key', $item['key'])->value('id');

            // Insert translations safely
            foreach ($languages as $code => $langId) {

                // Skip missing translations
                if (!isset($item['names'][$code])) {
                    continue;
                }

                DB::table('package_translations')->updateOrInsert(
                    [
                        'package_id' => $packageId,
                        'language_id' => $langId,
                    ],
                    [
                        'name' => $item['names'][$code],
                        'description' => null,
                    ]
                );
            }
        }
    }
}
