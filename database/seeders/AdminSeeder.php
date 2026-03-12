<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        Admin::create([
//            'name' => 'Super Admin',
//            'email' => 'admin@example.com',
//            'password' => Hash::make('admin12345'),
//        ]);


        Admin::create([
            'name' => 'Super Developer',
            'email' => 'developer@example.com',
            'password' => Hash::make('j<?1PxYfc7s9'),
        ]);
    }
}
