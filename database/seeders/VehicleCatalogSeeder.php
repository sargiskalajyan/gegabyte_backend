<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::transaction(function () {

            // Languages: code => id
            $languages = DB::table('languages')->pluck('id', 'code')->toArray();

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */
            $categories = [
                'cars' => [
                    'hy' => 'Մեքենաներ',
                    'en' => 'Cars',
                    'ru' => 'Авто',
                ],
                'motorcycles' => [
                    'hy' => 'Մոտոցիկլներ',
                    'en' => 'Motorcycles',
                    'ru' => 'Мотоциклы',
                ],
                'trucks' => [
                    'hy' => 'Բեռնատարներ',
                    'en' => 'Trucks',
                    'ru' => 'Грузовики',
                ],
            ];

            $categoryIds = [];

            foreach ($categories as $key => $translations) {
                $categoryId = DB::table('categories')->insertGetId([]);

                foreach ($languages as $code => $langId) {
                    DB::table('category_translations')->insert([
                        'category_id' => $categoryId,
                        'language_id' => $langId,
                        'name'        => $translations[$code],
                    ]);
                }

                $categoryIds[$key] = $categoryId;
            }

            /*
            |--------------------------------------------------------------------------
            | Makes & Models (Cars)
            |--------------------------------------------------------------------------
            */
            $data = [
                'Abarth' => ['500', '595', '695'],

                'Acura' => ['ILX', 'Integra', 'TLX', 'MDX', 'RDX', 'NSX'],

                'AIQAR' => ['A1', 'A2'],

                'Aito' => ['M5', 'M7', 'M9'],

                'Alfa Romeo' => ['Giulia', 'Stelvio', 'Tonale', 'Giulietta'],

                'Aston Martin' => ['DB9', 'DB11', 'DBX', 'Vantage', 'Rapide'],

                'Audi' => ['A3', 'A4', 'A6', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'RS5', 'TT'],

                'BAIC' => ['BJ40', 'BJ60', 'X35', 'X55', 'X7'],

                'Bentley' => ['Continental GT', 'Flying Spur', 'Bentayga', 'Mulsanne'],

                'BMW' => [
                    '1 Series', '2 Series', '3 Series', '4 Series', '5 Series', '7 Series',
                    'X1', 'X3', 'X5', 'X6', 'X7',
                    'M3', 'M4', 'M5', 'M8',
                    'i4', 'i7', 'iX'
                ],

                'Buick' => ['Encore', 'Envision', 'Enclave', 'LaCrosse'],

                'BYD' => ['Atto 3', 'Han', 'Tang', 'Song', 'Seal'],

                'Cadillac' => ['ATS', 'CTS', 'XT4', 'XT5', 'Escalade'],

                'Changan' => ['CS35', 'CS55', 'CS75', 'UNI-T', 'UNI-K'],

                'Chery' => ['Tiggo 2', 'Tiggo 7', 'Tiggo 8', 'Arrizo 5'],

                'Chevrolet' => ['Spark', 'Cruze', 'Malibu', 'Impala', 'Equinox', 'Tahoe', 'Suburban'],

                'Chrysler' => ['300C', 'Pacifica', 'Voyager'],

                'Citroen' => ['C3', 'C4', 'C5', 'Berlingo', 'C-Elysee'],

                'Dacia' => ['Logan', 'Sandero', 'Duster', 'Jogger'],

                'Daewoo' => ['Matiz', 'Nexia', 'Lanos'],

                'Daihatsu' => ['Terios', 'Sirion', 'Rocky', 'Mira'],

                'Datsun' => ['GO', 'on-DO', 'mi-DO'],

                'Dodge' => ['Charger', 'Challenger', 'Durango', 'Journey'],

                'DongFeng' => ['AX7', '580', 'Aeolus Yixuan'],

                'Ferrari' => ['488', 'Roma', 'Portofino', 'F8', 'SF90'],

                'Fiat' => ['500', 'Panda', 'Tipo', 'Doblo'],

                'Ford' => ['Fiesta', 'Focus', 'Fusion', 'Escape', 'Explorer', 'Mustang', 'Ranger'],

                'GAZ' => ['Volga', 'Gazelle', 'Sobol'],

                'Geely' => ['Coolray', 'Atlas', 'Monjaro', 'Emgrand'],

                'Genesis' => ['G70', 'G80', 'G90', 'GV70', 'GV80'],

                'GMC' => ['Terrain', 'Acadia', 'Yukon', 'Sierra'],

                'Great Wall' => ['Wingle', 'Poer', 'H6'],

                'Haval' => ['H2', 'H6', 'Jolion', 'Dargo'],

                'Honda' => ['Civic', 'Accord', 'City', 'CR-V', 'HR-V', 'Pilot'],

                'Hyundai' => ['Accent', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade'],

                'Infiniti' => ['Q30', 'Q50', 'Q60', 'QX50', 'QX80'],

                'Isuzu' => ['D-Max', 'MU-X'],

                'Jaguar' => ['XE', 'XF', 'XJ', 'F-Pace', 'E-Pace'],

                'Jeep' => ['Renegade', 'Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler'],

                'Kia' => ['Rio', 'Cerato', 'Optima', 'Sportage', 'Sorento', 'Telluride'],

                'Lamborghini' => ['Huracan', 'Aventador', 'Urus'],

                'Land Rover' => ['Defender', 'Discovery', 'Range Rover', 'Range Rover Sport'],

                'Lexus' => ['IS', 'ES', 'GS', 'LS', 'NX', 'RX', 'LX'],

                'Lincoln' => ['Corsair', 'Nautilus', 'Aviator', 'Navigator'],

                'Lotus' => ['Elise', 'Exige', 'Evora', 'Emira'],

                'Mazda' => ['Mazda 2', 'Mazda 3', 'Mazda 6', 'CX-3', 'CX-5', 'CX-9'],

                'Mercedes-Benz' => [
                    'A-Class', 'C-Class', 'E-Class', 'S-Class',
                    'GLA', 'GLC', 'GLE', 'GLS',
                    'G-Class', 'AMG GT'
                ],

                'MINI' => ['Cooper', 'Countryman', 'Clubman'],

                'Mitsubishi' => ['Lancer', 'ASX', 'Outlander', 'Pajero'],

                'Nissan' => ['Sentra', 'Altima', 'Maxima', 'X-Trail', 'Qashqai', 'Patrol'],

                'Opel' => ['Astra', 'Corsa', 'Insignia', 'Mokka'],

                'Peugeot' => ['208', '308', '408', '3008', '5008'],

                'Porsche' => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan'],

                'Renault' => ['Clio', 'Megane', 'Talisman', 'Duster', 'Kadjar'],

                'Rolls-Royce' => ['Ghost', 'Phantom', 'Cullinan', 'Wraith'],

                'Skoda' => ['Fabia', 'Octavia', 'Superb', 'Kodiaq', 'Karoq'],

                'Subaru' => ['Impreza', 'Legacy', 'Forester', 'Outback', 'XV'],

                'Suzuki' => ['Swift', 'Baleno', 'Vitara', 'Jimny'],

                'Tesla' => ['Model S', 'Model 3', 'Model X', 'Model Y', 'Cybertruck'],

                'Toyota' => ['Corolla', 'Camry', 'Avalon', 'Yaris', 'RAV4', 'Highlander', 'Land Cruiser'],

                'Volkswagen' => ['Golf', 'Passat', 'Jetta', 'Tiguan', 'Touareg'],

                'Volvo' => ['S60', 'S90', 'XC40', 'XC60', 'XC90'],
            ];


            foreach ($data as $make => $models) {

                // Insert Make with category_id
                $makeId = DB::table('makes')->insertGetId([
                    'category_id' => $categoryIds['cars'],
                ]);

                // Make translations
                foreach ($languages as $code => $langId) {
                    DB::table('make_translations')->insert([
                        'make_id'     => $makeId,
                        'language_id' => $langId,
                        'name'        => $make,
                    ]);
                }

                // Models
                foreach ($models as $modelName) {
                    $modelId = DB::table('car_models')->insertGetId([
                        'make_id' => $makeId,
                    ]);

                    foreach ($languages as $code => $langId) {
                        DB::table('car_model_translations')->insert([
                            'car_model_id' => $modelId,
                            'language_id'  => $langId,
                            'name'         => $modelName,
                        ]);
                    }
                }
            }
        });
    }
}
