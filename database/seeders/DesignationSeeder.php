<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            ['title' => 'Chief Executive Officer', 'department' => 'Executive', 'level' => 'C-Level', 'salary_range_min' => 100000, 'salary_range_max' => 200000],
            ['title' => 'Chief Operating Officer', 'department' => 'Operations', 'level' => 'C-Level', 'salary_range_min' => 90000, 'salary_range_max' => 180000],
            ['title' => 'Chief Technology Officer', 'department' => 'Technology', 'level' => 'C-Level', 'salary_range_min' => 90000, 'salary_range_max' => 170000],
            ['title' => 'Software Engineer', 'department' => 'IT', 'level' => 'Mid-Level', 'salary_range_min' => 40000, 'salary_range_max' => 80000],
            ['title' => 'Junior Software Engineer', 'department' => 'IT', 'level' => 'Entry-Level', 'salary_range_min' => 25000, 'salary_range_max' => 50000],
            ['title' => 'Senior Software Engineer', 'department' => 'IT', 'level' => 'Senior-Level', 'salary_range_min' => 60000, 'salary_range_max' => 100000],
            ['title' => 'Project Manager', 'department' => 'Management', 'level' => 'Mid-Level', 'salary_range_min' => 50000, 'salary_range_max' => 90000],
            ['title' => 'Human Resources Manager', 'department' => 'Human Resources', 'level' => 'Mid-Level', 'salary_range_min' => 50000, 'salary_range_max' => 85000],
            ['title' => 'Marketing Manager', 'department' => 'Marketing', 'level' => 'Mid-Level', 'salary_range_min' => 45000, 'salary_range_max' => 85000],
            ['title' => 'Sales Executive', 'department' => 'Sales', 'level' => 'Entry-Level', 'salary_range_min' => 25000, 'salary_range_max' => 50000],
            ['title' => 'Accountant', 'department' => 'Finance', 'level' => 'Mid-Level', 'salary_range_min' => 30000, 'salary_range_max' => 60000],
            ['title' => 'Graphic Designer', 'department' => 'Design', 'level' => 'Entry-Level', 'salary_range_min' => 20000, 'salary_range_max' => 45000],
        ];

        foreach ($designations as $designation) {
            DB::table('designations')->insert([
                'title' => $designation['title'],
                'slug' => Str::slug($designation['title']),
                'department' => $designation['department'],
                'level' => $designation['level'],
                'salary_range_min' => $designation['salary_range_min'],
                'salary_range_max' => $designation['salary_range_max'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
