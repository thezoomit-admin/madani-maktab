<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Companies in the tech industry focusing on software, hardware, and IT services.',
            ],
            [
                'name' => 'Healthcare',
                'slug' => 'healthcare',
                'description' => 'Companies providing health services, medical devices, pharmaceuticals, and healthcare products.',
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Financial services and institutions such as banks, insurance companies, and investment firms.',
            ],
            [
                'name' => 'Retail',
                'slug' => 'retail',
                'description' => 'Companies in the business of selling goods and services to consumers.',
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'description' => 'Companies that produce and distribute goods, from consumer products to industrial machinery.',
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Companies involved in providing educational services, tutoring, or learning materials.',
            ],
            [
                'name' => 'Construction',
                'slug' => 'construction',
                'description' => 'Companies that build infrastructure such as buildings, roads, and bridges.',
            ],
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Companies that buy, sell, or lease properties and manage real estate investments.',
            ],
            [
                'name' => 'Transportation',
                'slug' => 'transportation',
                'description' => 'Companies engaged in the movement of goods and people by land, sea, or air.',
            ],
            [
                'name' => 'Hospitality',
                'slug' => 'hospitality',
                'description' => 'Companies involved in lodging, food service, and tourism industries.',
            ],
        ];
 
        DB::table('company_categories')->insert($categories);
    }
}
