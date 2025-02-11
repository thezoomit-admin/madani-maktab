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
            'কিতাব',
            'স্বাভাবিক-মাযেরাত',
            'নিবন্ধিত-তালিবে-ইলম',
            'প্রাথমিক-পরীক্ষায়-মাযেরাত',
            'প্রাথমিক-পরীক্ষায়-উত্তীর্ণ',
            'চূড়ান্ত-পরীক্ষায়-মাযেরাত',
            'চূড়ান্ত-পরীক্ষায়-উত্তীর্ণ',
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
