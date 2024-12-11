<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'name' => 'Admin',
            'slug' => 'admin', 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],[
            'name' => 'Student',
            'slug' => 'student', 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Admin role created successfully!');
    }
}
