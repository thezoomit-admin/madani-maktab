<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('countries')->insert([
            // United States
            [
                'iso' => 'US',
                'name' => 'United States',
                'iso3' => 'USA',
                'numcode' => 840,
                'phonecode' => 1,
                'nationality' => 'American',
            ],
            // Canada
            [
                'iso' => 'CA',
                'name' => 'Canada',
                'iso3' => 'CAN',
                'numcode' => 124,
                'phonecode' => 1,
                'nationality' => 'Canadian',
            ],
            // Mexico
            [
                'iso' => 'MX',
                'name' => 'Mexico',
                'iso3' => 'MEX',
                'numcode' => 484,
                'phonecode' => 52,
                'nationality' => 'Mexican',
            ],
            // Brazil
            [
                'iso' => 'BR',
                'name' => 'Brazil',
                'iso3' => 'BRA',
                'numcode' => 76,
                'phonecode' => 55,
                'nationality' => 'Brazilian',
            ],
            // United Kingdom
            [
                'iso' => 'GB',
                'name' => 'United Kingdom',
                'iso3' => 'GBR',
                'numcode' => 826,
                'phonecode' => 44,
                'nationality' => 'British',
            ],
            // Germany
            [
                'iso' => 'DE',
                'name' => 'Germany',
                'iso3' => 'DEU',
                'numcode' => 276,
                'phonecode' => 49,
                'nationality' => 'German',
            ],
            // France
            [
                'iso' => 'FR',
                'name' => 'France',
                'iso3' => 'FRA',
                'numcode' => 250,
                'phonecode' => 33,
                'nationality' => 'French',
            ],
            // Italy
            [
                'iso' => 'IT',
                'name' => 'Italy',
                'iso3' => 'ITA',
                'numcode' => 380,
                'phonecode' => 39,
                'nationality' => 'Italian',
            ],
            // Spain
            [
                'iso' => 'ES',
                'name' => 'Spain',
                'iso3' => 'ESP',
                'numcode' => 724,
                'phonecode' => 34,
                'nationality' => 'Spanish',
            ],
            // China
            [
                'iso' => 'CN',
                'name' => 'China',
                'iso3' => 'CHN',
                'numcode' => 156,
                'phonecode' => 86,
                'nationality' => 'Chinese',
            ],
            // Japan
            [
                'iso' => 'JP',
                'name' => 'Japan',
                'iso3' => 'JPN',
                'numcode' => 392,
                'phonecode' => 81,
                'nationality' => 'Japanese',
            ],
            // Australia
            [
                'iso' => 'AU',
                'name' => 'Australia',
                'iso3' => 'AUS',
                'numcode' => 36,
                'phonecode' => 61,
                'nationality' => 'Australian',
            ],
            // India
            [
                'iso' => 'IN',
                'name' => 'India',
                'iso3' => 'IND',
                'numcode' => 356,
                'phonecode' => 91,
                'nationality' => 'Indian',
            ],
            // South Africa
            [
                'iso' => 'ZA',
                'name' => 'South Africa',
                'iso3' => 'ZAF',
                'numcode' => 710,
                'phonecode' => 27,
                'nationality' => 'South African',
            ],
            // Russia
            [
                'iso' => 'RU',
                'name' => 'Russia',
                'iso3' => 'RUS',
                'numcode' => 643,
                'phonecode' => 7,
                'nationality' => 'Russian',
            ],
            // Argentina
            [
                'iso' => 'AR',
                'name' => 'Argentina',
                'iso3' => 'ARG',
                'numcode' => 32,
                'phonecode' => 54,
                'nationality' => 'Argentine',
            ],
            // Egypt
            [
                'iso' => 'EG',
                'name' => 'Egypt',
                'iso3' => 'EGY',
                'numcode' => 818,
                'phonecode' => 20,
                'nationality' => 'Egyptian',
            ],
            // Saudi Arabia
            [
                'iso' => 'SA',
                'name' => 'Saudi Arabia',
                'iso3' => 'SAU',
                'numcode' => 682,
                'phonecode' => 966,
                'nationality' => 'Saudi',
            ],
            // Turkey
            [
                'iso' => 'TR',
                'name' => 'Turkey',
                'iso3' => 'TUR',
                'numcode' => 792,
                'phonecode' => 90,
                'nationality' => 'Turkish',
            ],
            // Nigeria
            [
                'iso' => 'NG',
                'name' => 'Nigeria',
                'iso3' => 'NGA',
                'numcode' => 566,
                'phonecode' => 234,
                'nationality' => 'Nigerian',
            ],
            // Pakistan
            [
                'iso' => 'PK',
                'name' => 'Pakistan',
                'iso3' => 'PAK',
                'numcode' => 586,
                'phonecode' => 92,
                'nationality' => 'Pakistani',
            ],
            // Indonesia
            [
                'iso' => 'ID',
                'name' => 'Indonesia',
                'iso3' => 'IDN',
                'numcode' => 360,
                'phonecode' => 62,
                'nationality' => 'Indonesian',
            ],
        ]);
    }
}
