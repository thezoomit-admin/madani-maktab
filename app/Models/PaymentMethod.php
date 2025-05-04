<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory; 
    protected $fillable = [
        'name',
        'icon',
        'info',
        'income_in_hand',
        'expense_in_hand',
        'balance',
    ];
}
