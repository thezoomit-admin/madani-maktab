<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory; 


    public static function generateNextCustomerId(){
        $largest_user_id = Customer::where('customer_id', 'like', 'PID-%') 
        ->pluck('customer_id')
                ->map(function ($id) {
                        return preg_replace("/[^0-9]/", "", $id);
                }) 
        ->max(); 
        $largest_user_id++; 
        $new_user_id = 'PID-' . str_pad($largest_user_id, 6, '0', STR_PAD_LEFT);
        return $new_user_id;
    }

    public static function generateNextUserCustomerId(){
        $largest_user_id = Customer::where('customer_id', 'like', 'CUS-%')
        ->pluck('customer_id')
                ->map(function ($id) {
                        return preg_replace("/[^0-9]/", "", $id);
                }) 
        ->max();  
        $largest_user_id++; 
        $new_user_id = 'CUS-' . str_pad($largest_user_id, 6, '0', STR_PAD_LEFT);
        return $new_user_id;
    }
}
