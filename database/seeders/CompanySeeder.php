<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            [
                'name' => 'ABC Technologies',
                'website' => 'https://www.abctech.com',
                'address' => '123 Tech Avenue, Silicon Valley, USA',
                'logo' => 'abc_logo.png',
                'primary_color' => '#ff5733',
                'secondary_color' => '#33c1ff',
                'founded_date' => Carbon::create('2010', '01', '01'),
                'is_active' => true,
                'category_id' => 1, 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'XYZ Innovations',
                'website' => 'https://www.xyzinnovations.com',
                'address' => '456 Innovation Blvd, San Francisco, USA',
                'logo' => 'xyz_logo.png',
                'primary_color' => '#34a853',
                'secondary_color' => '#0b9eac',
                'founded_date' => Carbon::create('2015', '05', '15'),
                'is_active' => true,
                'category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
        ]);
    }
}
