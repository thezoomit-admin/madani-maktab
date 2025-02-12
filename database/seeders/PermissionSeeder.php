<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'ড্যাশবোর্ড',
            'মক্তব',  
            'মক্তব-স্বাভাবিক-মাযেরাত',
            'মক্তব-নিবন্ধিত-তালিবে-ইলম',
            'মক্তব-প্রাথমিক-পরীক্ষায়-মাযেরাত',
            'মক্তব-প্রাথমিক-পরীক্ষায়-উত্তীর্ণ',
            'মক্তব-চূড়ান্ত-পরীক্ষায়-মাযেরাত',
            'মক্তব-চূড়ান্ত-পরীক্ষায়-উত্তীর্ণ',
            'কিতাব',
            'কিতাব-স্বাভাবিক-মাযেরাত',
            'কিতাব-নিবন্ধিত-তালিবে-ইলম',
            'কিতাব-প্রাথমিক-পরীক্ষায়-মাযেরাত',
            'কিতাব-প্রাথমিক-পরীক্ষায়-উত্তীর্ণ',
            'কিতাব-চূড়ান্ত-পরীক্ষায়-মাযেরাত',
            'কিতাব-চূড়ান্ত-পরীক্ষায়-উত্তীর্ণ'  
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission, 
                'slug' => $permission, 
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
