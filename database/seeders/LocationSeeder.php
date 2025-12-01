<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // disable FK checks for truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('location_translations')->truncate();
        DB::table('locations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // get language ids
        $languages = DB::table('languages')->pluck('id', 'code')->toArray();
        $en = $languages['en'];
        $hy = $languages['hy'];
        $ru = $languages['ru'];

        // parent locations (Yerevan + marzes)
        $parents = [
            // Yerevan - will be parent for districts
            [
                'key' => 'yerevan',
                'names' => ['en' => 'Yerevan', 'hy' => 'Երևան', 'ru' => 'Ереван'],
                'children' => [
                    // 12 administrative districts of Yerevan (list.am style)
                    ['en' => 'Ajapnyak', 'hy' => 'Աջափնյակ', 'ru' => 'Ачапняк'],
                    ['en' => 'Arabkir', 'hy' => 'Արաբկիր', 'ru' => 'Арабкир'],
                    ['en' => 'Avan', 'hy' => 'Ավան', 'ru' => 'Аван'],
                    ['en' => 'Davtashen', 'hy' => 'Դավթաշեն', 'ru' => 'Давташен'],
                    ['en' => 'Erebuni', 'hy' => 'Էրեբունի', 'ru' => 'Эребуни'],
                    ['en' => 'Kanaker-Zeytun', 'hy' => 'Կանաքեռ-Զեյթուն', 'ru' => 'Канакер-Зейтун'],
                    ['en' => 'Kentron', 'hy' => 'Կենտրոն', 'ru' => 'Кентрон'],
                    ['en' => 'Malatia-Sebastia', 'hy' => 'Մալաթիա-Սեբաստիա', 'ru' => 'Малатия-Себастия'],
                    ['en' => 'Nor Nork', 'hy' => 'Նոր Նորք', 'ru' => 'Нор Норк'],
                    ['en' => 'Nork-Marash', 'hy' => 'Նորգ-Մարաշ', 'ru' => 'Норк-Мараш'],
                    ['en' => 'Shengavit', 'hy' => 'Շենգավիթ', 'ru' => 'Шенгавит'],
                    ['en' => 'Nubarashen', 'hy' => 'Նուբարաշեն', 'ru' => 'Нубарашен'],
                ],
            ],

            // Marzes (list.am style — common cities/towns)
            [
                'key' => 'aragatsotn',
                'names' => ['en' => 'Aragatsotn', 'hy' => 'Արագածոտն', 'ru' => 'Арагацотн'],
                'children' => [
                    ['en' => 'Ashtarak', 'hy' => 'Աշտարակ', 'ru' => 'Аштарак'],
                    ['en' => 'Aparan', 'hy' => 'Ապարան', 'ru' => 'Апаран'],
                    ['en' => 'Talin', 'hy' => 'Թալին', 'ru' => 'Талин'],
                    ['en' => 'Byurakan', 'hy' => 'Բյուրական', 'ru' => 'Бюракан'],
                ],
            ],

            [
                'key' => 'ararat',
                'names' => ['en' => 'Ararat', 'hy' => 'Արարատ', 'ru' => 'Арарат'],
                'children' => [
                    ['en' => 'Artashat', 'hy' => 'Արտաշատ', 'ru' => 'Арташат'],
                    ['en' => 'Masis', 'hy' => 'Մասիս', 'ru' => 'Масис'],
                    ['en' => 'Ararat', 'hy' => 'Արարատ', 'ru' => 'Арарат (г.)'],
                    ['en' => 'Vedi', 'hy' => 'Վեդի', 'ru' => 'Веди'],
                ],
            ],

            [
                'key' => 'armavir',
                'names' => ['en' => 'Armavir', 'hy' => 'Արմավիր', 'ru' => 'Армавир'],
                'children' => [
                    ['en' => 'Armavir', 'hy' => 'Արմավիր', 'ru' => 'Армавир (г.)'],
                    ['en' => 'Echmiadzin', 'hy' => 'Էջմիածին', 'ru' => 'Эчмиадзин'],
                    ['en' => 'Parakar', 'hy' => 'Փարաքար', 'ru' => 'Паракар'],
                    ['en' => 'Merdzavan', 'hy' => 'Մերձավան', 'ru' => 'Мердзаван'],
                    ['en' => 'Arevashat', 'hy' => 'Արևաշատ', 'ru' => 'Аревошат'],
                    ['en' => 'Argavand', 'hy' => 'Արգավանդ', 'ru' => 'Аргаванд'],
                    ['en' => 'Baghramyan', 'hy' => 'Բաղրամյան', 'ru' => 'Баграмян'],
                ],
            ],

            [
                'key' => 'gegharkunik',
                'names' => ['en' => 'Gegharkunik', 'hy' => 'Գեղարքունիք', 'ru' => 'Гехаркюник'],
                'children' => [
                    ['en' => 'Sevan', 'hy' => 'Սևան', 'ru' => 'Севан'],
                    ['en' => 'Gavar', 'hy' => 'Գավառ', 'ru' => 'Гавар'],
                    ['en' => 'Martuni', 'hy' => 'Մարտունի', 'ru' => 'Мартуни'],
                    ['en' => 'Vardenis', 'hy' => 'Վարդենիս', 'ru' => 'Варденис'],
                ],
            ],

            [
                'key' => 'kotayk',
                'names' => ['en' => 'Kotayk', 'hy' => 'Կոտայք', 'ru' => 'Котайк'],
                'children' => [
                    ['en' => 'Hrazdan', 'hy' => 'Հրազդան', 'ru' => 'Раздан'],
                    ['en' => 'Abovyan', 'hy' => 'Աբովյան', 'ru' => 'Абовян'],
                    ['en' => 'Byureghavan', 'hy' => 'Բյուրեղավան', 'ru' => 'Бюрехаван'],
                    ['en' => 'Tsaghkadzor', 'hy' => 'Ծաղկաձոր', 'ru' => 'Цахкадзор'],
                ],
            ],

            [
                'key' => 'lori',
                'names' => ['en' => 'Lori', 'hy' => 'Լոռի', 'ru' => 'Лори'],
                'children' => [
                    ['en' => 'Vanadzor', 'hy' => 'Վանաձոր', 'ru' => 'Ванадзор'],
                    ['en' => 'Stepanavan', 'hy' => 'Ստեփանավան', 'ru' => 'Степанаван'],
                    ['en' => 'Alaverdi', 'hy' => 'Ալավերդի', 'ru' => 'Алаверди'],
                    ['en' => 'Spitak', 'hy' => 'Սպիտակ', 'ru' => 'Спитак'],
                ],
            ],

            [
                'key' => 'shirak',
                'names' => ['en' => 'Shirak', 'hy' => 'Շիրակ', 'ru' => 'Ширак'],
                'children' => [
                    ['en' => 'Gyumri', 'hy' => 'Գյումրի', 'ru' => 'Гюмри'],
                    ['en' => 'Artik', 'hy' => 'Արտիկ', 'ru' => 'Артик'],
                    ['en' => 'Maralik', 'hy' => 'Մարալիկ', 'ru' => 'Маралик'],
                ],
            ],

            [
                'key' => 'syunik',
                'names' => ['en' => 'Syunik', 'hy' => 'Սյունիք', 'ru' => 'Сюник'],
                'children' => [
                    ['en' => 'Kapan', 'hy' => 'Քաջարան', 'ru' => 'Капан'],
                    ['en' => 'Goris', 'hy' => 'Գորիս', 'ru' => 'Горис'],
                    ['en' => 'Sisian', 'hy' => 'Սիսիան', 'ru' => 'Сисиан'],
                    ['en' => 'Meghri', 'hy' => 'Մեղրի', 'ru' => 'Мегри'],
                ],
            ],

            [
                'key' => 'tavush',
                'names' => ['en' => 'Tavush', 'hy' => 'Տավուշ', 'ru' => 'Тавуш'],
                'children' => [
                    ['en' => 'Ijevan', 'hy' => 'Իջևան', 'ru' => 'Иджеван'],
                    ['en' => 'Berd', 'hy' => 'Բերդ', 'ru' => 'Берд'],
                    ['en' => 'Noyemberyan', 'hy' => 'Նոյեմբերյան', 'ru' => 'Нойемберян'],
                    ['en' => 'Dilijan', 'hy' => 'Դիլիջան', 'ru' => 'Дилижан'],
                ],
            ],

            [
                'key' => 'vayots_dzor',
                'names' => ['en' => 'Vayots Dzor', 'hy' => 'Վայոց Ձոր', 'ru' => 'Вайоц Дзор'],
                'children' => [
                    ['en' => 'Yeghegnadzor', 'hy' => 'Եղեգնաձոր', 'ru' => 'Ехегнадзор'],
                    ['en' => 'Vayk', 'hy' => 'Վայք', 'ru' => 'Вайк'],
                    ['en' => 'Jermuk', 'hy' => 'Ջերմուկ', 'ru' => 'Джермук'],
                ],
            ],
        ];

        // Insert parents and children
        foreach ($parents as $parent) {
            // insert parent location
            $parentId = DB::table('locations')->insertGetId([
                'parent_id' => null,
            ]);

            // prepare translations for parent
            $translations = [
                ['location_id' => $parentId, 'language_id' => $en, 'name' => $parent['names']['en']],
                ['location_id' => $parentId, 'language_id' => $hy, 'name' => $parent['names']['hy']],
                ['location_id' => $parentId, 'language_id' => $ru, 'name' => $parent['names']['ru']],
            ];
            DB::table('location_translations')->insert($translations);

            // insert children
            foreach ($parent['children'] as $child) {
                $childId = DB::table('locations')->insertGetId([
                    'parent_id' => $parentId,
                ]);

                $childTranslations = [
                    ['location_id' => $childId, 'language_id' => $en, 'name' => $child['en']],
                    ['location_id' => $childId, 'language_id' => $hy, 'name' => $child['hy']],
                    ['location_id' => $childId, 'language_id' => $ru, 'name' => $child['ru']],
                ];
                DB::table('location_translations')->insert($childTranslations);
            }
        }
    }
}
