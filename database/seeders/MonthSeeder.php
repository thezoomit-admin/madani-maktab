<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = [
            'রমজান', 
            'শাওয়াল', 
            'জিলকদ', 
            'জিলহজ্জ', 
            'মহররম', 
            'সফর', 
            'রবিউল আউয়াল', 
            'রবিউস সানি', 
            'জমাদিউল আউয়াল', 
            'জমাদিউস সানি', 
            'রজব', 
            'শাবান'
        ];

        foreach ($months as $month) {
            DB::table('hijri_months')->insert([
                'month' => $month, 
            ]);
        }
    }
}
