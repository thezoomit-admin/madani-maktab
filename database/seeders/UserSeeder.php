<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'role_id' => 1, 
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),  
            'dob' => '1990-01-01',
            'gender' => 1,   
            'blood_group' => 1,  
            'created_by' => 1,
            'updated_by' => 1,
        ]);
 
        User::create([
            'name' => 'John Doe',
            'role_id' => 1, 
            'email' => 'john@example.com',
            'phone' => '0987654321',
            'password' => Hash::make('password123'),  
            'dob' => '1995-03-10',
            'gender' => 2,   
            'blood_group' => 2,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
