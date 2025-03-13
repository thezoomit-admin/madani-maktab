<?php

namespace Database\Seeders;

use App\Models\HijriYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            '১৪৩৬', 
            '১৪৩৭', 
            '১৪৩৮', 
            '১৪৩৯', 
            '১৪৪০',
            '১৪৪১', 
            '১৪৪২', 
            '১৪৪৩', 
            '১৪৪৪', 
            '১৪৪৫',
            '১৪৪৬',
            '১৪৪৭', 
            '১৪৪৮', 
            '১৪৪৯', 
            '১৪৫০', 
            '১৪৫১',
            '১৪৫২', 
            '১৪৫৩', 
            '১৪৫৪', 
            '১৪৫৫', 
            '১৪৫৬'
        ];  

        foreach( $datas as $data){ 
            HijriYear::create([
                'year' => $data
            ]);
        }
    }
}
