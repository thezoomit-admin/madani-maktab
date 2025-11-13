<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        $permissions = [
            'dashboard' => 'ড্যাশবোর্ড',
            'maktab-entry' => 'মক্তব-দাখেলা',
            'kitab-entry' => 'কিতাব-দাখেলা',
            'talibe-ilm' => 'তালিবে-ইলম',
            'payment-list' => 'পেমেন্ট-লিস্ট',
            'payment-approval' => 'পেমেন্ট-অনুমোদন',
            'income-expense-report' => 'আয়-ব্যয়ের প্রতিবেদন',
            'ajifa-report' => 'অজিফা প্রতিবেদন',
            'ajifa-collection-report' => 'অজিফা সংগ্রহ প্রতিবেদন',
            'department-deposit' => 'দফতরের জমা',
            'department-deposit-report' => 'দফতরের জমা প্রতিবেদন',
            'expense' => 'খরচ',
            'expense-report' => 'খরচের প্রতিবেদন',
            'due-report' => 'বকেয়া প্রতিবেদন',
            'payment-report' => 'পরিশোধ প্রতিবেদন',
            'monthly-expense-report' => 'মাসিক ব্যয়ের প্রতিবেদন',
            'receive' => 'গ্রহন',
            'receive-report' => 'গ্রহনের রিপোর্ট',
            'log' => 'লগ',
            'settings' => 'সেটিংস',
            'payment_history' => 'পেমেন্ট ইতিহাস',
            'profile_change' => 'প্রোফাইল পরিবর্তন',
            'teacher' => 'খাদেম',
        ];
 
        foreach ($permissions as $slug => $name) {
            DB::table('permissions')->insert([
                'name' => $name,
                'slug' => Str::slug($slug, '-'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('✅ Permissions seeded successfully!');
    }
}
