<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {  
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class); 
        $this->call(UserSeeder::class); 
        $this->call(PermissionSeeder::class);    
        $this->call(CountrySeeder::class);        
        $this->call(DivisionSeeder::class);  
        $this->call(DistrictSeeder::class);  
        $this->call(UpazilaSeeder::class);  
        $this->call(UnionSeeder::class);  
        $this->call(CompanyCategorySeeder::class);  
        $this->call(DesignationSeeder::class);  
    }
}
