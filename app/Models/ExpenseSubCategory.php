<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseSubCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'categoriy_id',  
        'name',
        'description',
    ]; 

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'categoriy_id'); 
    }
}
